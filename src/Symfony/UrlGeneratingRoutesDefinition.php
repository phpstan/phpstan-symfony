<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface UrlGeneratingRoutesDefinition
{

	public function getName(): string;

	public function getController(): ?string;

}
