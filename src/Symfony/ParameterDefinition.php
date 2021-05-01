<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ParameterDefinition
{

	public function getKey(): string;

	/**
	 * @return array<mixed>|bool|float|int|string
	 */
	public function getValue();

}
