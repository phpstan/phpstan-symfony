<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;
use function class_exists;
use function interface_exists;
use const PHP_VERSION_ID;

/**
 * @extends RuleTestCase<ContainerInterfacePrivateServiceRule>
 */
final class ContainerInterfacePrivateServiceRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfacePrivateServiceRule((new XmlServiceMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/container.xml'])))->create());
	}

	public function testGetPrivateService(): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
		}
		$this->analyse(
			[
				__DIR__ . '/ExampleController.php',
			],
			[
				[
					'Service "private" is private.',
					13,
				],
			]
		);
	}

	public function testGetPrivateServiceInLegacyServiceSubscriber(): void
	{
		if (!interface_exists('Symfony\\Component\\DependencyInjection\\ServiceSubscriberInterface')) {
			self::markTestSkipped('The test needs Symfony\Component\DependencyInjection\ServiceSubscriberInterface class.');
		}

		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
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

		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
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

	public function testGetPrivateServiceWithoutAutowireLocatorAttribute(): void
	{
		if (PHP_VERSION_ID < 80000) {
			self::markTestSkipped('The test uses PHP Attributes which are available since PHP 8.0.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleAutowireLocatorEmptyService.php',
			],
			[
				[
					'Service "private" is private.',
					22,
				],
			]
		);
	}

	public function testGetPrivateServiceViaAutowireLocatorAttribute(): void
	{
		if (PHP_VERSION_ID < 80000) {
			self::markTestSkipped('The test uses PHP Attributes which are available since PHP 8.0.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleAutowireLocatorService.php',
			],
			[]
		);
	}

}
