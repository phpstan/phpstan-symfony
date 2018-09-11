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
		if ($scope->getClassReflection() === null) {
			return [];
		}
		$classReflection = $scope->getClassReflection()->getNativeReflection();
		$classname = $scope->getClassReflection()->getName();
		if ($classReflection->isInterface() || $classReflection->isAnonymous()) {
			return [];
		}
		if ($this->isSymfonyService($classname) &&
			!$this->isDependencyInjectionMethod($node, $classname) &&
			$this->modifiesInternalState($node)) {
			return [
				sprintf(
					'%s->%s modifies internal state of a Symfony service',
					$classReflection->getName(),
					(string) $node->name
				),
			];
		}

		return [];
	}

	private function isSymfonyService(string $classname): bool
	{
		$serviceId = $this->serviceMap->getServiceIdsFromClassname($classname);

		return $serviceId !== null && count($serviceId) > 0;
	}

	private function isDependencyInjectionMethod(Node $parserNode, string $classname): bool
	{
		$serviceIds = $this->serviceMap->getServiceIdsFromClassname($classname);
		if ($serviceIds === null) {
			return false;
		}
		foreach ($serviceIds as $serviceId) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service === null) {
				continue;
			}
			foreach ($service->getMethodCalls() as $curMethodCall) {
				if (!isset($parserNode->name)) { // Can be PhpParser\Node\Stmt\If_ that has no name
					continue;
				}
				if ($curMethodCall === $parserNode->name) {
					return true;
				}
			}
		}

		return false;
	}

	private function modifiesInternalState(Node $parserNode): bool
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
					if (!isset($parserNode->name)) { // Can be PhpParser\Node\Stmt\If_ that has no name
						return false;
					}

					return true;
				}
			} elseif ($this->modifiesInternalState($statement)) {
				return true;
			}
		}

		return false;
	}

}
