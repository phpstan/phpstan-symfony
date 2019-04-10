<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\ObjectType;
use function sprintf;

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
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof MethodCall) {
			throw new ShouldNotHappenException();
		}

		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->name->name !== 'get' || !isset($node->args[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);

		$isTestContainerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Test\TestContainer'))->isSuperTypeOf($argType);
		$isOldServiceSubscriber = (new ObjectType('Symfony\Component\DependencyInjection\ServiceSubscriberInterface'))->isSuperTypeOf($argType);
		$isServiceSubscriber = (new ObjectType('Symfony\Contracts\Service\ServiceSubscriberInterface'))->isSuperTypeOf($argType);
		if ($isTestContainerType->yes() || $isOldServiceSubscriber->yes() || $isServiceSubscriber->yes()) {
			return [];
		}

		$isControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller'))->isSuperTypeOf($argType);
		$isAbstractControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType);
		$isContainerType = (new ObjectType('Symfony\Component\DependencyInjection\ContainerInterface'))->isSuperTypeOf($argType);
		if (!$isControllerType->yes() && !$isAbstractControllerType->yes() && !$isContainerType->yes()) {
			return [];
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($node->args[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && !$service->isPublic()) {
				return [sprintf('Service "%s" is private.', $serviceId)];
			}
		}

		return [];
	}

}
