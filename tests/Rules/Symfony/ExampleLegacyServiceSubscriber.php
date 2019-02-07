<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

final class ExampleLegacyServiceSubscriber implements ServiceSubscriberInterface
{
	public function privateService(): void
	{
		$this->get('private');
	}

	/**
	 * @return string[]
	 */
	public static function getSubscribedServices(): array
	{
		return [];
	}

}
