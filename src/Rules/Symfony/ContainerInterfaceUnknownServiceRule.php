<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Symfony\Helper;

final class ContainerInterfaceUnknownServiceRule implements Rule
{

	/** @var ServiceMap */
	private $serviceMap;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(ServiceMap $symfonyServiceMap, Standard $printer)
	{
		$this->serviceMap = $symfonyServiceMap;
		$this->printer = $printer;
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
		$isAbstractControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType);
		$isContainerType = (new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))->isSuperTypeOf($argType);
		if (!$isControllerType->yes() && !$isAbstractControllerType->yes() && !$isContainerType->yes()) {
			return [];
		}

		$serviceId = ServiceMap::getServiceIdFromNode($node->args[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service === null && !$scope->isSpecified(Helper::createMarkerNode($node->var, $scope->getType($node->args[0]->value), $this->printer))) {
				return [sprintf('Service "%s" is not registered in the container.', $serviceId)];
			}
		}

		return [];
	}

}
