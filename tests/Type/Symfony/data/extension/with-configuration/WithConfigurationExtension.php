<?php

namespace PHPStan\Type\Symfony\Extension\WithConfiguration;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Extension\Extension;

class WithConfigurationExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container): void
	{
		\PHPStan\Testing\assertType(
			Configuration::class,
			$this->getConfiguration($configs, $container)
		);
	}
}
