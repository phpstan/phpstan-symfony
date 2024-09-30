<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use function array_map;

final class DefaultParameterMap implements ParameterMap
{

	/** @var ParameterDefinition[] */
	private array $parameters;

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
		$strings = $scope->getType($node)->getConstantStrings();

		return array_map(static fn (Type $type) => $type->getValue(), $strings);
	}

}
