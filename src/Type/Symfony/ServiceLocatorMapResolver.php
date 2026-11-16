<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_key_exists;
use function count;
use function get_object_vars;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function ltrim;
use function strpos;
use function substr;

final class ServiceLocatorMapResolver
{

	private Parser $parser;

	private InitializerExprTypeResolver $initializerResolver;

	private TypeStringResolver $typeStringResolver;

	/** @var array<string, Class_|null> */
	private array $classes = [];

	public function __construct(Parser $parser, InitializerExprTypeResolver $initializerResolver, TypeStringResolver $typeStringResolver)
	{
		$this->parser = $parser;
		$this->initializerResolver = $initializerResolver;
		$this->typeStringResolver = $typeStringResolver;
	}

	public function parameter(ClassReflection $class, string $method, string $parameter): ?Type
	{
		if (!$class->hasNativeMethod($method)) {
			return null;
		}
		$relevant = $class->implementsInterface('Symfony\Contracts\Service\ServiceSubscriberInterface');
		foreach ($class->getNativeMethod($method)->getVariants()[0]->getParameters() as $reflectionParameter) {
			if ($reflectionParameter->getName() !== $parameter) {
				continue;
			}
			foreach ($reflectionParameter->getAttributes() as $attributeReflection) {
				$relevant = $relevant || $attributeReflection->getName() === 'Symfony\Component\DependencyInjection\Attribute\AutowireLocator';
			}
		}
		if (!$relevant) {
			return null;
		}
		$node = $this->getClass($class);
		$methodNode = $node !== null ? $node->getMethod($method) : null;
		foreach ($methodNode !== null ? $methodNode->getParams() : [] as $param) {
			if (!$param->var instanceof Variable || $param->var->name !== $parameter) {
				continue;
			}
			foreach ($param->attrGroups as $group) {
				foreach ($group->attrs as $attribute) {
					if ($attribute->name->toString() === 'Symfony\Component\DependencyInjection\Attribute\AutowireLocator') {
						$services = $this->argument($attribute->args, 'services', 0);
						return $services === null ? $this->unknown() : $this->map($services, $class);
					}
					if ($attribute->name->toString() === 'Symfony\Component\DependencyInjection\Attribute\Autowire') {
						return null;
					}
				}
			}
		}
		if ($class->implementsInterface('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
			foreach ($class->getNativeMethod($method)->getVariants()[0]->getParameters() as $paramReflection) {
				if ($paramReflection->getName() === $parameter && in_array($paramReflection->getType()->getObjectClassNames(), [['Psr\Container\ContainerInterface'], ['Symfony\Component\DependencyInjection\ContainerInterface']], true)) {
					return $this->subscriber($class);
				}
			}
		}
		return null;
	}

	public function property(ClassReflection $class, string $property): ?Type
	{
		if ($property === 'container' && $class->implementsInterface('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
			for ($owner = $class; $owner !== null; $owner = $owner->getParentClass()) {
				if ($this->usesSubscriberTrait($owner)) {
					return $this->subscriber($class);
				}
			}
		}
		if (!$class->hasConstructor()) {
			return null;
		}
		$class = $class->getConstructor()->getDeclaringClass();
		$relevant = $class->implementsInterface('Symfony\Contracts\Service\ServiceSubscriberInterface');
		foreach ($class->getConstructor()->getVariants()[0]->getParameters() as $constructorParameter) {
			foreach ($constructorParameter->getAttributes() as $attributeReflection) {
				$relevant = $relevant || $attributeReflection->getName() === 'Symfony\Component\DependencyInjection\Attribute\AutowireLocator';
			}
		}
		if (!$relevant) {
			return null;
		}
		$node = $this->getClass($class);
		$constructor = $node !== null ? $node->getMethod('__construct') : null;
		if ($constructor === null) {
			return null;
		}
		foreach ($constructor->getParams() as $param) {
			if ($param->flags !== 0 && $param->var instanceof Variable && $param->var->name === $property) {
				return $this->parameter($class, '__construct', $property);
			}
		}
		$aliases = [];
		foreach ($constructor->getStmts() ?? [] as $statement) {
			if (!$statement instanceof Expression || !$statement->expr instanceof Assign) {
				continue;
			}
			$assign = $statement->expr;
			if ($assign->var instanceof Variable && is_string($assign->var->name)) {
				$aliases[$assign->var->name] = $assign->expr instanceof Variable && is_string($assign->expr->name) ? ($aliases[$assign->expr->name] ?? $assign->expr->name) : null;
			}
			if ($assign->var instanceof PropertyFetch && $assign->var->var instanceof Variable && $assign->var->var->name === 'this' && $assign->var->name instanceof Identifier && $assign->var->name->name === $property && $assign->expr instanceof Variable && is_string($assign->expr->name)) {
				$name = array_key_exists($assign->expr->name, $aliases) ? $aliases[$assign->expr->name] : $assign->expr->name;
				return $name === null ? null : $this->parameter($class, '__construct', $name);
			}
		}
		return null;
	}

	private function subscriber(ClassReflection $class): ?Type
	{
		$parent = $class->getParentClass();
		$inherited = $parent !== null && $parent->implementsInterface('Symfony\Contracts\Service\ServiceSubscriberInterface') ? $this->subscriber($parent) : null;
		$node = $this->getClass($class);
		if ($node === null) {
			return $inherited;
		}
		$declared = $node->getMethod('getSubscribedServices');
		if ($declared !== null) {
			foreach ($declared->getStmts() ?? [] as $statement) {
				if ($statement instanceof Return_ && $statement->expr !== null) {
					$map = $this->map($statement->expr, $class);
					return count($map->getConstantArrays()) === 1 ? $map : null;
				}
			}
			return null;
		}
		if (!$this->usesSubscriberTrait($class)) {
			return $inherited;
		}
		$builder = ConstantArrayTypeBuilder::createEmpty();
		if ($inherited !== null) {
			$arrays = $inherited->getConstantArrays();
			if (count($arrays) !== 1) {
				return $this->unknown();
			}
			foreach ($arrays[0]->getKeyTypes() as $i => $key) {
				$builder->setOffsetValueType($key, $arrays[0]->getValueTypes()[$i], $arrays[0]->isOptionalKey($i));
			}
		}
		foreach ($node->getMethods() as $method) {
			$attribute = $this->subscribedAttribute($method->attrGroups);
			if ($attribute === null || $method->isStatic() || $method->isAbstract()) {
				continue;
			}
			$variant = $class->getNativeMethod($method->name->name)->getVariants()[0];
			$type = $variant->getNativeReturnType();
			$this->addSubscribed($builder, $attribute->args, $class->getName() . '::' . $method->name->name, TypeCombinator::removeNull($type), TypeCombinator::containsNull($type), $class);
		}
		foreach ($this->usesSubscriberTrait($class, true) ? $node->getProperties() : [] as $property) {
			$attribute = $this->subscribedAttribute($property->attrGroups);
			if ($attribute === null || !$this->hasGetHook($property)) {
				continue;
			}
			$name = $property->props[0]->name->name;
			$type = $class->getNativeProperty($name)->getNativeType();
			$this->addSubscribed($builder, $attribute->args, $class->getName() . '::$' . $name . '::get', TypeCombinator::removeNull($type), TypeCombinator::containsNull($type), $class);
		}
		return $builder->getArray();
	}

	private function usesSubscriberTrait(ClassReflection $class, bool $properties = false): bool
	{
		$names = $properties ? ['Symfony\Contracts\Service\ServiceMethodsSubscriberTrait'] : ['Symfony\Contracts\Service\ServiceMethodsSubscriberTrait', 'Symfony\Contracts\Service\ServiceSubscriberTrait'];
		foreach ($class->getTraits() as $trait) {
			if (in_array($trait->getName(), $names, true) || $this->usesSubscriberTrait($trait, $properties)) {
				return true;
			}
		}
		return false;
	}

	/** @param array<AttributeGroup> $groups */
	private function subscribedAttribute(array $groups): ?Attribute
	{
		foreach ($groups as $group) {
			foreach ($group->attrs as $attribute) {
				if ($attribute->name->toString() === 'Symfony\Contracts\Service\Attribute\SubscribedService') {
					return $attribute;
				}
			}
		}
		return null;
	}

	private function map(Expr $expr, ClassReflection $class): Type
	{
		$builder = ConstantArrayTypeBuilder::createEmpty();
		if (!$expr instanceof Array_) {
			$values = $this->value($expr, $class)->getConstantArrays();
			if (count($values) !== 1) {
				return $this->unknown();
			}
			foreach ($values[0]->getKeyTypes() as $i => $key) {
				if (!$this->addType($builder, $key->getValue(), $values[0]->getValueTypes()[$i])) {
					return $this->unknown();
				}
			}
			return $builder->getArray();
		}
		$entries = [];
		$next = 0;
		foreach ($expr->items as $item) {
			$item = $this->arrayItem($item);
			if ($item === null) {
				return $this->unknown();
			}
			$key = $item[0] === null ? $next++ : $this->key($this->value($item[0], $class));
			if ($key === null) {
				return $this->unknown();
			}
			$entries[$key] = $item[1];
			if (!is_int($key) || $key < $next) {
				continue;
			}

			$next = $key + 1;
		}
		foreach ($entries as $key => $value) {
			if ($value instanceof New_ && $value->class instanceof Name && $value->class->toString() === 'Symfony\Contracts\Service\Attribute\SubscribedService') {
				$keyExpr = $this->argument($value->getArgs(), 'key', 0);
				$typeExpr = $this->argument($value->getArgs(), 'type', 1);
				$type = $typeExpr === null ? null : $this->string($this->value($typeExpr, $class));
				$override = $keyExpr === null ? null : $this->string($this->value($keyExpr, $class));
				$key = $override ?? $key;
				if ($type === null) {
					if (is_int($key)) {
						return $this->unknown();
					}
					$builder->setOffsetValueType(new ConstantStringType($key), new MixedType(), true);
					continue;
				}
				$optionalExpr = $this->argument($value->getArgs(), 'nullable', 2);
				$optional = $optionalExpr !== null && !$this->value($optionalExpr, $class)->isFalse()->yes();
				$this->addType($builder, $key, new ConstantStringType(($optional ? '?' : '') . $type));
			} elseif (!$this->addType($builder, $key, $this->value($value, $class))) {
				return $this->unknown();
			}
		}
		return $builder->getArray();
	}

	/** @param array<Arg> $args */
	private function addSubscribed(ConstantArrayTypeBuilder $builder, array $args, string $key, Type $type, bool $optional, ClassReflection $class): void
	{
		$keyExpr = $this->argument($args, 'key', 0);
		$key = $keyExpr === null ? $key : ($this->string($this->value($keyExpr, $class)) ?? $key);
		$typeExpr = $this->argument($args, 'type', 1);
		if ($typeExpr !== null) {
			$typeString = $this->string($this->value($typeExpr, $class));
			if ($typeString !== null) {
				$type = TypeCombinator::removeNull($this->serviceType(ltrim($typeString, '?')));
				$optional = $optional || strpos($typeString, '?') === 0;
			}
		}
		$optionalExpr = $this->argument($args, 'nullable', 2);
		$optional = $optional || ($optionalExpr !== null && !$this->value($optionalExpr, $class)->isFalse()->yes());
		$builder->setOffsetValueType(new ConstantStringType($key), $type, $optional);
	}

	/** @param int|string $key */
	private function addType(ConstantArrayTypeBuilder $builder, $key, Type $value): bool
	{
		$type = $this->string($value);
		if ($type === null) {
			if (is_int($key)) {
				return false;
			}
			$builder->setOffsetValueType(new ConstantStringType($key), new MixedType());
			return true;
		}
		$optional = strpos($type, '?') === 0;
		$type = $optional ? substr($type, 1) : $type;
		$key = is_int($key) ? $type : $key;
		$builder->setOffsetValueType(new ConstantStringType($key), $this->serviceType($type), $optional);
		return true;
	}

	/** @return array{?Expr, Expr}|null */
	private function arrayItem(?ArrayItem $item): ?array
	{
		return $item === null || $item->unpack ? null : [$item->key, $item->value];
	}

	private function hasGetHook(Node $node): bool
	{
		// Property hooks aren't available in every supported parser version.
		$hooks = get_object_vars($node)['hooks'] ?? [];
		if (!is_array($hooks)) {
			return false;
		}
		foreach ($hooks as $hook) {
			$name = $hook instanceof Node ? (get_object_vars($hook)['name'] ?? null) : null;
			if ($name instanceof Identifier && $name->name === 'get') {
				return true;
			}
		}
		return false;
	}

	private function serviceType(string $type): Type
	{
		try {
			return $this->typeStringResolver->resolve($type);
		} catch (ParserException $e) {
			return new MixedType();
		}
	}

	private function value(Expr $expr, ClassReflection $class): Type
	{
		return $this->initializerResolver->getType($expr, InitializerExprContext::fromClassReflection($class));
	}

	/** @param array<Arg> $args */
	private function argument(array $args, string $name, int $position): ?Expr
	{
		foreach ($args as $i => $arg) {
			if (($arg->name !== null && $arg->name->name === $name) || ($arg->name === null && $i === $position)) {
				return $arg->value;
			}
		}
		return null;
	}

	/** @return int|string|null */
	private function key(Type $type)
	{
		$strings = $type->getConstantStrings();
		if (count($strings) === 1) {
			return $strings[0]->getValue();
		}
		$values = $type->getConstantScalarValues();
		return count($values) === 1 && is_int($values[0]) ? $values[0] : null;
	}

	private function string(Type $type): ?string
	{
		$strings = $type->getConstantStrings();
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

	private function unknown(): Type
	{
		return new ArrayType(new StringType(), new MixedType());
	}

	private function getClass(ClassReflection $class): ?Class_
	{
		$name = $class->getName();
		if (!array_key_exists($name, $this->classes)) {
			$file = $class->getFileName();
			$node = $file === null ? null : (new NodeFinder())->findFirst($this->parser->parseFile($file), static fn ($node): bool => $node instanceof Class_ && $node->namespacedName !== null && $node->namespacedName->toString() === $name);
			$this->classes[$name] = $node instanceof Class_ ? $node : null;
		}
		return $this->classes[$name];
	}

}
