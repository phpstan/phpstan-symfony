<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ExampleController extends Controller
{

	public function privateService(): void
	{
		$this->get('private');
	}

	public function privateServiceInTestContainer(): void
	{
		/** @var \Symfony\Bundle\FrameworkBundle\Test\TestContainer $container */
		$container = doFoo();
		$container->get('private');
	}

	public function unknownService(): void
	{
		$this->get('unknown');
	}

	public function unknownGuardedServiceInsideContext(): void
	{
		if ($this->has('unknown')) { // phpcs:ignore
			$this->get('unknown');
		}
	}

	public function unknownGuardedServiceOutsideOfContext(): void
	{
		if (!$this->has('unknown')) {
			return;
		}
		$this->get('unknown');
	}

}
