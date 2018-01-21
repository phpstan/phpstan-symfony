<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;

/**
 * @covers \PHPStan\Rules\Symfony\ContainerInterfaceUnknownServiceRule
 */
final class ContainerInterfaceUnknownServiceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$serviceMap = new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml');

		return new ContainerInterfaceUnknownServiceRule($serviceMap);
	}

	public function testGetUnknownService(): void
	{
		$this->analyse(
			[__DIR__ . '/ExampleController.php'],
			[
				[
					'Service "service.not.found" is not registered in the container.',
					19,
				],
				[
					'Service "PHPStan\Symfony\ServiceMap" is not registered in the container.',
					25,
				],
				[
					'Service "service.PHPStan\Symfony\ServiceMap" is not registered in the container.',
					37,
				],
				[
					'Service "PHPStan\Rules\Symfony\ExampleController" is not registered in the container.',
					43,
				],
			]
		);
	}

}
