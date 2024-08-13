<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ServiceTagDefinition
{

	public function getName(): string;

	public function getAttributes(): array;

}
