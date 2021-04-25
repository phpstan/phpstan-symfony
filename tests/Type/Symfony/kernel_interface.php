<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$kernel = new class ('dev', true) extends \Symfony\Component\HttpKernel\Kernel {

	public function registerBundles(): void
	{
	}

	public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader): void
	{
	}

};

assertType('string', $kernel->locateResource(''));
assertType('string', $kernel->locateResource('', null, true));
assertType('array<int, string>', $kernel->locateResource('', null, false));
