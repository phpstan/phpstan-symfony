<?php

namespace PHPStan\Type\Symfony\Extension\WithConfigurationWithConstructorRequiredParams;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Extension\Extension;

class WithConfigurationWithConstructorRequiredParamsExtension extends Extension
{
	public function load(array $configs, ContainerBuilder $container): void
	{
		\PHPStan\Testing\assertType(
			'null',
			$this->getConfiguration($configs, $container)
		);
	}
}
