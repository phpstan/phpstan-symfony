<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\ObjectType;

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

	/**
	 * @param MethodCall $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->name->name !== 'get' || !isset($node->args[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);
		$isControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller'))->isSuperTypeOf($argType);
		$isContainerType = (new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))->isSuperTypeOf($argType);
		if ((!$isControllerType->yes() && !$isContainerType->yes()) || $this->isTestContainer($scope)) {
			return [];
		}

		$serviceId = ServiceMap::getServiceIdFromNode($node->args[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && !$service->isPublic()) {
				return [sprintf('Service "%s" is private.', $serviceId)];
			}
		}

		return [];
	}

	private function isTestContainer(Scope $scope): bool
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null || !$classReflection->isSubclassOf('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase')) {
			return false;
		}

		$testContainer = $this->serviceMap->getService('test.service_container');
		if ($testContainer === null) {
			return false;
		}
		$class = $testContainer->getClass();

		return $testContainer->isPublic()
			&& $class !== null
			&& (new ObjectType('Symfony\Bundle\FrameworkBundle\Test\TestContainer'))->isSuperTypeOf(new ObjectType($class))->yes();
	}

}
