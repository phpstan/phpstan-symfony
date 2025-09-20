<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final class ExampleAutowireLocatorService
{

	public function __construct(
		#[AutowireLocator([
			'Foo' => 'Foo',
			'private' => 'Foo',
		])]
		private ContainerInterface $locator
	)
	{
	}

	public function privateServiceInLocator(): void
	{
		$this->locator->get('Foo');
		$this->locator->get('private');
	}

}
