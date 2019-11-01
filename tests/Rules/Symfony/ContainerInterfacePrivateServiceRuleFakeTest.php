<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;

final class ContainerInterfacePrivateServiceRuleFakeTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfacePrivateServiceRule((new XmlServiceMapFactory(null))->create());
	}

	public function testGetPrivateService(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleController.php',
			],
			[]
		);
	}

	public function testGetPrivateServiceInAbstractController(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleAbstractController.php',
			],
			[]
		);
	}

	public function testGetPrivateServiceInLegacyServiceSubscriber(): void
	{
		if (!interface_exists('Symfony\\Component\\DependencyInjection\\ServiceSubscriberInterface')) {
			self::markTestSkipped('The test needs Symfony\Component\DependencyInjection\ServiceSubscriberInterface class.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleLegacyServiceSubscriber.php',
				__DIR__ . '/ExampleLegacyServiceSubscriberFromAbstractController.php',
				__DIR__ . '/ExampleLegacyServiceSubscriberFromLegacyController.php',
			],
			[]
		);
	}

	public function testGetPrivateServiceInServiceSubscriber(): void
	{
		if (!interface_exists('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
			self::markTestSkipped('The test needs Symfony\Contracts\Service\ServiceSubscriberInterface class.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleServiceSubscriber.php',
				__DIR__ . '/ExampleServiceSubscriberFromAbstractController.php',
				__DIR__ . '/ExampleServiceSubscriberFromLegacyController.php',
			],
			[]
		);
	}

}
