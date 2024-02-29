<?php

namespace PHPStan\Type\Symfony\Extension\Anonymous;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

new class extends Extension
{
	public function load(array $configs, ContainerBuilder $container)
	{
		\PHPStan\Testing\assertType(
			'null',
			$this->getConfiguration($configs, $container)
		);
	}
};
