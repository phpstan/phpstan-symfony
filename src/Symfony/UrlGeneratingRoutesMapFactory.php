<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface UrlGeneratingRoutesMapFactory
{

	public function create(): UrlGeneratingRoutesMap;

}
