<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class InvalidArgumentDefaultValueRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new InvalidArgumentDefaultValueRule();
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleCommand.php',
			],
			[
				[
					'Parameter #4 $default of method Symfony\Component\Console\Command\Command::addArgument() expects string|null, int given.',
					22,
				],
				[
					'Parameter #4 $default of method Symfony\Component\Console\Command\Command::addArgument() expects string|null, array<int, string> given.',
					23,
				],
				[
					'Parameter #4 $default of method Symfony\Component\Console\Command\Command::addArgument() expects array<int, string>|null, array<string, string> given.',
					25,
				],
			]
		);
	}

}
