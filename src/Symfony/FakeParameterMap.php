<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class FakeParameterMap implements ParameterMap
{

	/**
	 * @return \PHPStan\Symfony\ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return null;
	}

}
