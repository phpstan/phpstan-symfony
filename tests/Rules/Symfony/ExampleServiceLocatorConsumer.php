<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Component\DependencyInjection\ServiceLocator;

final class ExampleServiceLocatorConsumer
{

	/**
	 * @param ServiceLocator<object> $locator
	 */
	public function __construct(private readonly ServiceLocator $locator)
	{
	}

	public function run(): void
	{
		// "unknown" is not a global container service id, but it is a valid locator
		// index key (e.g. from #[AutowireLocator]), so no serviceNotFound must be reported.
		$this->locator->get('unknown');
	}

}
