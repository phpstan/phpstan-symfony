<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function is_string;
use function strpos;

final class AutowireCallableTypeExtension implements ExpressionTypeResolverExtension, IgnoreErrorExtension
{

	private AutowireCallableTypeResolver $typeResolver;

	private Parser $parser;

	private bool $resolving = false;

	/** @var array<string, array<string, string>> */
	private array $assignedProperties = [];

	public function __construct(AutowireCallableTypeResolver $typeResolver, Parser $parser)
	{
		$this->typeResolver = $typeResolver;
		$this->parser = $parser;
	}

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($this->resolving) {
			return null;
		}
		$type = $this->getAutowiredType($expr, $scope);
		if ($type === null) {
			return null;
		}
		$this->resolving = true;
		try {
			$originalType = $scope->getType($expr);
		} finally {
			$this->resolving = false;
		}
		if (!$this->typeResolver->isUnspecifiedCallable($originalType)) {
			return null;
		}

		return TypeCombinator::intersect($originalType, TypeCombinator::union($type, new NullType()));
	}

	public function getAutowiredType(Expr $expr, Scope $scope): ?Type
	{
		$attributes = [];
		$parameterName = null;
		if ($expr instanceof Variable && is_string($expr->name) && $expr->name !== 'this') {
			if ($scope->isInAnonymousFunction()) {
				return null;
			}
			$function = $scope->getFunction();
			if ($function === null) {
				return null;
			}
			foreach ($function->getVariants()[0]->getParameters() as $parameter) {
				if ($parameter->getName() !== $expr->name) {
					continue;
				}
				$attributes = $parameter->getAttributes();
				$parameterName = $parameter->getName();
				break;
			}
		} elseif ($expr instanceof PropertyFetch && $expr->name instanceof Identifier) {
			$classes = $scope->getType($expr->var)->getObjectClassReflections();
			if (count($classes) !== 1 || !$classes[0]->hasNativeProperty($expr->name->name)) {
				return null;
			}
			$property = $classes[0]->getNativeProperty($expr->name->name);
			$attributes = $property->getAttributes();
			$parameterName = $expr->name->name;
			if ($attributes === []) {
				$parameter = $this->getAssignedParameter($property->getDeclaringClass(), $parameterName);
				if ($parameter !== null) {
					$attributes = $parameter->getAttributes();
					$parameterName = $parameter->getName();
				}
			}
		}
		if ($attributes === [] || $parameterName === null) {
			return null;
		}

		return $this->typeResolver->resolve($attributes, $parameterName, $scope)['type'] ?? null;
	}

	public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
	{
		if ($error->getIdentifier() !== 'missingType.callable') {
			return false;
		}
		if ($node instanceof InClassMethodNode) {
			foreach ($node->getMethodReflection()->getVariants()[0]->getParameters() as $parameter) {
				if (strpos($error->getMessage(), '$' . $parameter->getName() . ' ') === false) {
					continue;
				}
				$result = $this->typeResolver->resolve($parameter->getAttributes(), $parameter->getName(), $scope);
				return $result !== null && $result['type'] !== null && $result['error'] === null;
			}
		}
		if ($node instanceof ClassPropertyNode) {
			$property = $node->getClassReflection()->getNativeProperty($node->getName());
			$attributes = $property->getAttributes();
			$name = $node->getName();
			if ($attributes === []) {
				$parameter = $this->getAssignedParameter($property->getDeclaringClass(), $name);
				if ($parameter === null) {
					return false;
				}
				$attributes = $parameter->getAttributes();
				$name = $parameter->getName();
			}
			$result = $this->typeResolver->resolve($attributes, $name, $scope);
			return $result !== null && $result['type'] !== null && $result['error'] === null;
		}

		return false;
	}

	private function getAssignedParameter(ClassReflection $class, string $property): ?ExtendedParameterReflection
	{
		if (!$class->hasConstructor()) {
			return null;
		}
		$constructor = $class->getConstructor();
		$hasAttributes = false;
		foreach ($constructor->getVariants()[0]->getParameters() as $parameter) {
			if ($parameter->getAttributes() !== []) {
				$hasAttributes = true;
				break;
			}
		}
		if (!$hasAttributes) {
			return null;
		}
		$class = $constructor->getDeclaringClass();
		$key = $class->getName();
		if (!isset($this->assignedProperties[$key])) {
			$this->assignedProperties[$key] = [];
			$file = $class->getFileName();
			if ($file !== null) {
				$node = (new NodeFinder())->findFirst($this->parser->parseFile($file), static fn ($node): bool => $node instanceof Class_ && $node->namespacedName !== null && $node->namespacedName->toString() === $key);
				$method = $node instanceof Class_ ? $node->getMethod('__construct') : null;
				if ($method !== null) {
					foreach ($method->getParams() as $parameter) {
						if ($parameter->flags === 0 || !($parameter->var instanceof Variable) || !is_string($parameter->var->name)) {
							continue;
						}

						$this->assignedProperties[$key][$parameter->var->name] = $parameter->var->name;
					}
					foreach ($method->getStmts() ?? [] as $statement) {
						if (!$statement instanceof Expression || !$statement->expr instanceof Assign) {
							continue;
						}
						$assign = $statement->expr;
						if (!($assign->var instanceof PropertyFetch) || !($assign->var->var instanceof Variable) || $assign->var->var->name !== 'this' || !($assign->var->name instanceof Identifier) || !($assign->expr instanceof Variable) || !is_string($assign->expr->name)) {
							continue;
						}

						$this->assignedProperties[$key][$assign->var->name->name] = $assign->expr->name;
					}
				}
			}
		}
		$name = $this->assignedProperties[$key][$property] ?? null;
		foreach ($constructor->getVariants()[0]->getParameters() as $parameter) {
			if ($parameter->getName() === $name) {
				return $parameter;
			}
		}

		return null;
	}

}
