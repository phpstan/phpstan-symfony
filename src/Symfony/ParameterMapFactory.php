<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ParameterMapFactory
{

	public function create(): ParameterMap;

}
