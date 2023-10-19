<?php

namespace Rules\Symfony;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class ExampleServiceTraitSubscriber implements ServiceSubscriberInterface
{
	use ServiceSubscriberTrait;

	/** @var ContainerInterface */
	private $locator;

	public function __construct(ContainerInterface $locator)
	{
		$this->locator = $locator;
	}

	#[SubscribedService(attributes: new Autowire('private'))]
	private function getIndexed(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService(attributes: new Autowire(value: 'private'))]
	private function getNamedValue(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService(attributes: new Autowire(service: 'private'))]
	private function getNamedService(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService(attributes: new Autowire('unknown'))]
	private function getUnknownIndexed(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService(attributes: new Autowire(value: 'unknown'))]
	private function getUnknownNamedValue(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService(attributes: new Autowire(service: 'unknown'))]
	private function getUnknownNamedService(): Foo
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService]
	public function getBar(): \Bar
	{
		return $this->locator->get(__METHOD__);
	}

	#[SubscribedService]
	public function getBaz(): \Baz
	{
		return $this->locator->get(__METHOD__);
	}
}
