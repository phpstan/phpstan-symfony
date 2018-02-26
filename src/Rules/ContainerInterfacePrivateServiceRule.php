<?php
declare(strict_types=1);

namespace Lookyman\PHPStan\Symfony\Rules;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;

final class ContainerInterfacePrivateServiceRule implements Rule
{

	/**
	 * @var ServiceMap
	 */
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
		if ($node instanceof MethodCall && $node->name === 'get') {
			$type = $scope->getType($node->var);
			$baseController = new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller');
			$isInstanceOfController = $type instanceof ThisType && $baseController->isSuperTypeOf($type)->yes();
			$isContainerInterface = $type instanceof ObjectType && $type->getClassName() === 'Symfony\Component\DependencyInjection\ContainerInterface';
			if (($isContainerInterface || $isInstanceOfController)
				&& isset($node->args[0])
				&& $node->args[0] instanceof Arg
			) {
				$service = $this->serviceMap->getServiceFromNode($node->args[0]->value, $scope);
				if ($service !== \null && !$service['public']) {
					return [\sprintf('Service "%s" is private.', $service['id'])];
				}
			}
		}
		return [];
	}

}
