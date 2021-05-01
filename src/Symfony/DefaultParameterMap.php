<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeUtils;
use function count;

final class DefaultParameterMap implements ParameterMap
{

	/** @var \PHPStan\Symfony\ParameterDefinition[] */
	private $parameters;

	/**
	 * @param \PHPStan\Symfony\ParameterDefinition[] $parameters
	 */
	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * @return \PHPStan\Symfony\ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return $this->parameters[$key] ?? null;
	}

}
