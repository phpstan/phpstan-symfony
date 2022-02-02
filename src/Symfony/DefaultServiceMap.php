<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeUtils;
use function count;

final class DefaultServiceMap implements ServiceMap
{

	/** @var ServiceDefinition[] */
	private $services;

	/**
	 * @param ServiceDefinition[] $services
	 */
	public function __construct(array $services)
	{
		$this->services = $services;
	}

	/**
	 * @return ServiceDefinition[]
	 */
	public function getServices(): array
	{
		return $this->services;
	}

	public function getService(string $id): ?ServiceDefinition
	{
		return $this->services[$id] ?? null;
	}

	public static function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

}
