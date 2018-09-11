<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Symfony\ServiceMap;

class ModifiedServiceStateRule implements \PHPStan\Rules\Rule
{

	/** @var ServiceMap */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 * @throws \PHPStan\ShouldNotHappenException
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		if ($node->name->name === '__construct') {
			return [];
		}
		$classReflection = $scope->getClassReflection()->getNativeReflection();
		if ($classReflection->isInterface() || $classReflection->isAnonymous()) {
			return [];
		}
		if ($this->isSymfonyService($scope) &&
			!$this->isDependencyInjectionMethod($node, $scope) &&
			$this->modifiesInternalState($node, $scope)) {
			return [
				sprintf(
					'%s->%s modifies internal state of a Symfony service',
					$classReflection->getName(),
					(string)$node->name
				),
			];
		}

		return [];
	}

	private function isSymfonyService(Scope $scope): bool
	{
		$serviceId = $this->serviceMap->getServiceIdsFromClassname($scope->getClassReflection()->getName());

		return $serviceId != null && count($serviceId) > 0;
	}

	private function isDependencyInjectionMethod(Node $parserNode, Scope $scope): bool
	{
		$serviceIds = $this->serviceMap->getServiceIdsFromClassname($scope->getClassReflection()->getName());
		foreach ($serviceIds as $serviceId) {
			$service = $this->serviceMap->getService($serviceId);
			foreach ($service->getMethodCalls() as $curMethodCall) {
				if ($curMethodCall == $parserNode->name) {
					return true;
				}
			}
		}

		return false;
	}

	private function modifiesInternalState(Node $parserNode, Scope $scope): bool
	{
		if (!isset($parserNode->stmts)) {
			return false;
		}
		foreach ($parserNode->stmts as $statement) {
			if ($statement instanceof Node\Stmt\Expression) {
				$statement = $statement->expr;
			}
			if ($statement instanceof \PhpParser\Node\Expr\Assign) {
				if ($statement->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
					if (@!$parserNode->name) { // Can be PhpParser\Node\Stmt\If_ that has no name
						return false;
					}

					return true;
				}
			} elseif ($this->modifiesInternalState($statement, $scope)) {
				return true;
			}
		}

		return false;
	}
}