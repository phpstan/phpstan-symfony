<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ServiceTagDefinition
{

	public function getName(): string;

	/** @return array<string, string> */
	public function getAttributes(): array;

}
