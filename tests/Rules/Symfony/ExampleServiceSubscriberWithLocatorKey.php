<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use function PHPStan\Rules\Symfony\doFoo;

final class ExampleServiceSubscriberWithLocatorKey implements ServiceSubscriberInterface
{

	/** @var ContainerInterface */
	private $locator;

	public function __construct(ContainerInterface $locator)
	{
		$this->locator = $locator;
	}

	public function privateService(): void
	{
		$this->locator->get('foobar');
	}

	/**
	 * @return string[]
	 */
	public static function getSubscribedServices(): array
	{
		return [];
	}

}
