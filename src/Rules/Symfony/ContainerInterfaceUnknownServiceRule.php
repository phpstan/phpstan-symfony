<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Symfony\Helper;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class ContainerInterfaceUnknownServiceRule implements Rule
{

	/** @var ServiceMap */
	private $serviceMap;

	/** @var Standard */
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

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->name->name !== 'get' || !isset($node->getArgs()[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);
		$isContainerBagType = (new ObjectType('Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface'))->isSuperTypeOf($argType);
		if ($isContainerBagType->yes()) {
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

		$serviceId = $this->serviceMap->getServiceIdFromNode($node->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			$serviceIdType = $scope->getType($node->getArgs()[0]->value);
			if ($service === null && !$scope->getType(Helper::createMarkerNode($node->var, $serviceIdType, $this->printer))->equals($serviceIdType)) {
				return [
					RuleErrorBuilder::message(sprintf('Service "%s" is not registered in the container.', $serviceId))->build(),
				];
			}
		}

		return [];
	}

}
