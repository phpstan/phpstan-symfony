<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use function array_map;

final class DefaultParameterMap implements ParameterMap
{

	/** @var ParameterDefinition[] */
	private $parameters;

	/**
	 * @param ParameterDefinition[] $parameters
	 */
	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return $this->parameters[$key] ?? null;
	}

	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));

		return array_map(static function (Type $type) {
			return $type->getValue();
		}, $strings);
	}

}
