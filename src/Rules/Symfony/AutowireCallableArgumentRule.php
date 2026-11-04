<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NullType;
use PHPStan\Type\Symfony\AutowireCallableTypeExtension;
use PHPStan\Type\Symfony\AutowireCallableTypeResolver;
use PHPStan\Type\TypeCombinator;
use function count;
use function sprintf;

/** @implements Rule<Expr> */
final class AutowireCallableArgumentRule implements Rule
{

	private AutowireCallableTypeResolver $typeResolver;

	private ReflectionProvider $reflectionProvider;

	private AutowireCallableTypeExtension $typeExtension;

	public function __construct(AutowireCallableTypeResolver $typeResolver, ReflectionProvider $reflectionProvider, AutowireCallableTypeExtension $typeExtension)
	{
		$this->typeResolver = $typeResolver;
		$this->reflectionProvider = $reflectionProvider;
		$this->typeExtension = $typeExtension;
	}

	public function getNodeType(): string
	{
		return Expr::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof Assign && $node->var instanceof PropertyFetch && $node->var->name instanceof Identifier) {
			$classes = $scope->getType($node->var->var)->getObjectClassReflections();
			if (count($classes) !== 1 || !$classes[0]->hasNativeProperty($node->var->name->name)) {
				return [];
			}
			$declared = $classes[0]->getNativeProperty($node->var->name->name)->getReadableType();
			if (!$this->typeResolver->isUnspecifiedCallable($declared)) {
				return [];
			}
			$expected = $this->typeExtension->getAutowiredType($node->var, $scope);
			if ($expected !== null && TypeCombinator::containsNull($declared)) {
				$expected = TypeCombinator::union($expected, new NullType());
			}
			if ($expected !== null && $expected->isSuperTypeOf($scope->getType($node->expr))->no()) {
				return [RuleErrorBuilder::message(sprintf('Property $%s expects the signature declared by its autowiring attribute.', $node->var->name->name))
					->identifier('symfony.autowireCallableAssignment')
					->build()];
			}
			return [];
		}
		if (!$node instanceof CallLike || $node->isFirstClassCallable()) {
			return [];
		}
		$reflection = $this->getReflection($node, $scope);
		if ($reflection === null) {
			return [];
		}
		$parameters = ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $reflection->getVariants())->getParameters();
		$errors = [];
		foreach ($node->getArgs() as $i => $argument) {
			if ($argument->unpack) {
				continue;
			}
			$parameter = $parameters[$i] ?? null;
			if ($argument->name !== null) {
				$parameter = null;
				foreach ($parameters as $candidate) {
					if ($candidate->getName() === $argument->name->name) {
						$parameter = $candidate;
						break;
					}
				}
			}
			if (!$parameter instanceof ExtendedParameterReflection || $parameter->getAttributes() === []) {
				continue;
			}
			$declaredType = $parameter->getType();
			if (!$this->typeResolver->isUnspecifiedCallable($declaredType)) {
				continue;
			}
			$expected = $this->typeResolver->resolve($parameter->getAttributes(), $parameter->getName(), $scope)['type'] ?? null;
			if ($expected === null) {
				continue;
			}
			if (TypeCombinator::containsNull($declaredType)) {
				$expected = TypeCombinator::union($expected, new NullType());
			}
			$actual = $scope->getType($argument->value);
			if ($declaredType->isSuperTypeOf($actual)->no() || !$expected->isSuperTypeOf($actual)->no()) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf('Parameter $%s expects the signature declared by its autowiring attribute.', $parameter->getName()))
				->identifier('symfony.autowireCallableArgument')
				->line($argument->getStartLine())
				->build();
		}

		return $errors;
	}

	/** @return ExtendedMethodReflection|FunctionReflection|null */
	private function getReflection(CallLike $node, Scope $scope)
	{
		if ($node instanceof FuncCall && $node->name instanceof Name && $this->reflectionProvider->hasFunction($node->name, $scope)) {
			return $this->reflectionProvider->getFunction($node->name, $scope);
		}
		if ($node instanceof MethodCall && $node->name instanceof Identifier) {
			$type = $scope->getType($node->var);
			return $type->hasMethod($node->name->name)->yes() ? $type->getMethod($node->name->name, $scope) : null;
		}
		if (($node instanceof New_ || $node instanceof StaticCall) && $node->class instanceof Name) {
			$className = $scope->resolveName($node->class);
			if (!$this->reflectionProvider->hasClass($className)) {
				return null;
			}
			$class = $this->reflectionProvider->getClass($className);
			if ($node instanceof New_) {
				return $class->hasConstructor() ? $class->getConstructor() : null;
			}
			if ($node->name instanceof Identifier && $class->hasMethod($node->name->name)) {
				return $class->getMethod($node->name->name, $scope);
			}
		}

		return null;
	}

}
