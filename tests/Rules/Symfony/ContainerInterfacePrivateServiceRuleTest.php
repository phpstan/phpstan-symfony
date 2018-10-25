<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;

final class ContainerInterfacePrivateServiceRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfacePrivateServiceRule((new XmlServiceMapFactory(__DIR__ . '/container.xml'))->create());
	}

	public function testGetPrivateService(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleController.php',
			],
			[
				[
					'Service "private" is private.',
					12,
				],
			]
		);
	}

}
