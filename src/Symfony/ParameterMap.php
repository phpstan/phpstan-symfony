<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ParameterMap
{

	/**
	 * @return \PHPStan\Symfony\ParameterDefinition[]
	 */
	public function getParameters(): array;

	public function getParameter(string $key): ?ParameterDefinition;

}
