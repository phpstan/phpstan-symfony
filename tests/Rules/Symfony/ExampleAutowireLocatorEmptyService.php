<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final class ExampleAutowireLocatorEmptyService
{

	public function __construct(
		#[AutowireLocator([])]
		private ContainerInterface $locator
	)
	{
		$this->locator = $locator;
	}

	public function privateServiceInLocator(): void
	{
		$this->locator->get('Foo');
		$this->locator->get('private');
	}

}
