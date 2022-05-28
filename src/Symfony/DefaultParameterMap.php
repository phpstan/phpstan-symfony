<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use function array_map;
use function count;

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

	/**
	 * @deprecated Will be removed in 2.0
	 */
	public static function getParameterKeyFromNode(Expr $node, Scope $scope): ?string
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

	/**
	 * @return array<string>
	 */
	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));

		return array_map(static function (Type $type) {
			return $type->getValue();
		}, $strings);
	}

}
