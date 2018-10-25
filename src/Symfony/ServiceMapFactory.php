<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ServiceMapFactory
{

	public function create(): ServiceMap;

}
