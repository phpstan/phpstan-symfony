<?php declare(strict_types = 1);

namespace SymfonyServiceLocator;

use Symfony\Component\DependencyInjection\ServiceLocator;
use function PHPStan\Testing\assertType;

interface HandlerInterface
{

	public function handle(): void;

}

class Consumer
{

	/**
	 * @param ServiceLocator<HandlerInterface> $locator
	 */
	public function __construct(
		private readonly ServiceLocator $locator,
	)
	{
	}

	public function run(string $key): void
	{
		// has() on a ServiceLocator must NOT collapse to a constant bool: a locator key
		// is a local index, not a global service id, so the ServiceMap cannot decide it.
		// Otherwise a `if (!$locator->has('x')) { return; }` guard would be seen as
		// "always true" and everything after it as unreachable.
		assertType('bool', $this->locator->has($key));
		assertType('bool', $this->locator->has('some_index'));
	}

}
