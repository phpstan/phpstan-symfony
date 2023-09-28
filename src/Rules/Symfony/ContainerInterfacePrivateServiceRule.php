<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Symfony\ServiceMap;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class ContainerInterfacePrivateServiceRule implements Rule
{

	/** @var ServiceMap */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->name->name !== 'get' || !isset($node->getArgs()[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);

		$isTestContainerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Test\TestContainer'))->isSuperTypeOf($argType);
		$isOldServiceSubscriber = (new ObjectType('Symfony\Component\DependencyInjection\ServiceSubscriberInterface'))->isSuperTypeOf($argType);
		$isServiceSubscriber = $this->isServiceSubscriber($argType, $scope);
		$isServiceProvider = $this->isServiceProvider($argType, $scope);
		// ServiceLocator is an implementation of ServiceProviderInterface but let's keep explicit rule for it to keep full BC
		$isServiceLocator = (new ObjectType('Symfony\Component\DependencyInjection\ServiceLocator'))->isSuperTypeOf($argType);
		if (
			$isTestContainerType->yes()
			|| $isOldServiceSubscriber->yes()
			|| $isServiceSubscriber->yes()
			|| $isServiceProvider->yes()
			|| $isServiceLocator->yes()
		) {
			return [];
		}

		$isControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller'))->isSuperTypeOf($argType);
		$isAbstractControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType);
		$isContainerType = (new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))->isSuperTypeOf($argType);
		$isPsrContainerType = (new ObjectType('Psr\Container\ContainerInterface'))->isSuperTypeOf($argType);
		if (
			!$isControllerType->yes()
			&& !$isAbstractControllerType->yes()
			&& !$isContainerType->yes()
			&& !$isPsrContainerType->yes()
		) {
			return [];
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($node->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && !$service->isPublic()) {
				return [
					RuleErrorBuilder::message(sprintf('Service "%s" is private.', $serviceId))
						->identifier('symfonyContainer.privateService')
						->build(),
				];
			}
		}

		return [];
	}

	private function isServiceSubscriber(Type $containerType, Scope $scope): TrinaryLogic
	{
		return $this->isInstanceOf(
			new ObjectType('Symfony\Contracts\Service\ServiceSubscriberInterface'),
			$containerType,
			$scope
		);
	}

	private function isServiceProvider(Type $containerType, Scope $scope): TrinaryLogic
	{
		return $this->isInstanceOf(
			new ObjectType('Symfony\Contracts\Service\ServiceProviderInterface'),
			$containerType,
			$scope
		);
	}

	private function isInstanceOf(ObjectType $interfaceType, Type $containerType, Scope $scope): TrinaryLogic
	{
		$isImplementation = $interfaceType->isSuperTypeOf($containerType);
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return $isImplementation;
		}
		$containedClassType = new ObjectType($classReflection->getName());
		return $isImplementation->or($interfaceType->isSuperTypeOf($containedClassType));
	}

}
