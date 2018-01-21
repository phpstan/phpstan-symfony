<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;

/**
 * @covers \PHPStan\Rules\Symfony\ContainerInterfacePrivateServiceRule
 */
final class ContainerInterfacePrivateServiceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$serviceMap = new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml');

		return new ContainerInterfacePrivateServiceRule($serviceMap);
	}

	public function testGetPrivateService(): void
	{
		$this->analyse(
			[__DIR__ . '/ExampleController.php'],
			[
				[
					'Service "private" is private.',
					13,
				],
			]
		);
	}

}
