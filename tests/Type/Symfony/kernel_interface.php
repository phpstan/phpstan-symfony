<?php declare(strict_types = 1);

$kernel = new class ('dev', true) extends \Symfony\Component\HttpKernel\Kernel {

	public function registerBundles(): void
	{
	}

	public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader): void
	{
	}

};

$foo = $kernel->locateResource('');
$bar = $kernel->locateResource('', null, true);
$baz = $kernel->locateResource('', null, false);

die;
