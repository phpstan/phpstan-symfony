<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class ExampleServiceSubscriberWithLocatorKey implements ServiceSubscriberInterface
{

	/** @var ContainerInterface */
	private $locator;

	public function __construct(ContainerInterface $locator)
	{
		$this->locator = $locator;
	}

	public function privateAliasService(): void
	{
		$this->locator->get('private_alias');
	}

	public function publicAliasService(): void
	{
		$this->locator->get('public_alias');
	}

	/**
	 * @return string[]
	 */
	public static function getSubscribedServices(): array
	{
		return [];
	}

}
