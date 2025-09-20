<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;
use function class_exists;
use function interface_exists;
use const PHP_VERSION_ID;

/**
 * @extends RuleTestCase<ContainerInterfaceUnknownServiceRule>
 */
final class ContainerInterfaceUnknownServiceRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfaceUnknownServiceRule((new XmlServiceMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/container.xml'])))->create(), new Standard());
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
					'Service "unknown" is not registered in the container.',
					25,
				],
			]
		);
	}

	public function testGetPrivateServiceInAbstractController(): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleAbstractController.php',
			],
			[
				[
					'Service "unknown" is not registered in the container.',
					25,
				],
			]
		);
	}

	public function testGetPrivateServiceInLegacyServiceSubscriber(): void
	{
		if (!interface_exists('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
			self::markTestSkipped('The test needs Symfony\Contracts\Service\ServiceSubscriberInterface class.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleServiceSubscriber.php',
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
					'Service "Foo" is not registered in the AutowireLocator.',
					21,
				],
				[
					'Service "private" is not registered in the AutowireLocator.',
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

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
		];
	}

}
