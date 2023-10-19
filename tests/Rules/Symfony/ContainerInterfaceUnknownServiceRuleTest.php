<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;
use function class_exists;
use function interface_exists;

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

	public function testGetPrivateServiceInServiceSubscriberWithTrait(): void
	{
		if (!interface_exists('Symfony\Contracts\Service\ServiceSubscriberInterface')) {
			self::markTestSkipped('The test needs Symfony\Contracts\Service\ServiceSubscriberInterface class.');
		}

		if (!trait_exists('Symfony\Contracts\Service\ServiceSubscriberTrait')) {
			self::markTestSkipped('The test needs Symfony\Contracts\Service\ServiceSubscriberTrait class.');
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleServiceTraitSubscriber.php',
			],
			[
				[
					'Service "unknown" is not registered in the container.',
					44,
				],
				[
					'Service "unknown" is not registered in the container.',
					50,
				],
				[
					'Service "unknown" is not registered in the container.',
					56,
				],
				[
					'Service "Baz" is not registered in the container.',
					68,
				],
			]
		);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
		];
	}

}
