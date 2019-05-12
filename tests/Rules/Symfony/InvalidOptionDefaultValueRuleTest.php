<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class InvalidOptionDefaultValueRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new InvalidOptionDefaultValueRule();
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleCommand.php',
			],
			[
				[
					'Parameter #5 $default of method Symfony\Component\Console\Command\Command::addOption() expects array<string>|null, array<int, int> given.',
					29,
				],
			]
		);
	}

}
