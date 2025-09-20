<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\TypedReference;
use function class_exists;
use function count;
use function sprintf;

final class AutowireLocatorServiceMapFactory implements ServiceMapFactory
{

	/** @var Node */
	private $node;

	/** @var Scope */
	private $scope;

	public function __construct(
		Node $node,
		Scope $scope
	)
	{
		$this->node = $node;
		$this->scope = $scope;
	}

	public function create(): ServiceMap
	{
		if (!class_exists('Symfony\\Component\\DependencyInjection\\Attribute\\AutowireLocator')) {
			return new FakeServiceMap();
		}

		if (!$this->node instanceof MethodCall) {
			return new FakeServiceMap();
		}

		$nodeParentProperty = $this->node->var;

		if (!$nodeParentProperty instanceof Node\Expr\PropertyFetch) {
			return new FakeServiceMap();
		}

		$nodeParentPropertyName = $nodeParentProperty->name;

		if (!$nodeParentPropertyName instanceof Node\Identifier) {
			return new FakeServiceMap();
		}

		$containerInterfacePropertyName = $nodeParentPropertyName->name;
		$scopeClassReflection = $this->scope->getClassReflection();

		if (!$scopeClassReflection instanceof ClassReflection) {
			return new FakeServiceMap();
		}

		$containerInterfacePropertyReflection = $scopeClassReflection
			->getNativeProperty($containerInterfacePropertyName);
		$classPropertyReflection = $containerInterfacePropertyReflection->getNativeReflection();
		$autowireLocatorAttributesReflection = $classPropertyReflection->getAttributes(AutowireLocator::class);

		if (count($autowireLocatorAttributesReflection) === 0) {
			return new FakeServiceMap();
		}

		if (count($autowireLocatorAttributesReflection) > 1) {
			throw new InvalidArgumentException(sprintf(
				'Only one AutowireLocator attribute is allowed on "%s::%s".',
				$scopeClassReflection->getName(),
				$containerInterfacePropertyName
			));
		}

		$autowireLocatorAttributeReflection = $autowireLocatorAttributesReflection[0];
		/** @var AutowireLocator $autowireLocator */
		$autowireLocator = $autowireLocatorAttributeReflection->newInstance();
		$serviceLocatorArgument = $autowireLocator->value;

		if (!$serviceLocatorArgument instanceof ServiceLocatorArgument) {
			return new FakeServiceMap();
		}

		/** @var Service[] $services */
		$services = [];

		/** @var TypedReference $service */
		foreach ($serviceLocatorArgument->getValues() as $id => $service) {
			$class = $service->getType();
			$alias = $service->getName();

			$services[$id] = new Service(
				$id,
				$class,
				true,
				false,
				$alias
			);
		}

		return new DefaultServiceMap($services);
	}

}
