<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;

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
			[__DIR__ . '/../../Symfony/data/ExampleController.php'],
			[
				[
					'Service "private" is private.',
					14,
				],
			]
		);
	}

	public function testGetPrivateServiceOnTestContainer(): void
	{
		$this->analyse(
			[__DIR__ . '/../../Symfony/data/KernelTestExample.php'],
			[]
		);
	}

}
