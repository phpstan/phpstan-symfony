<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InvalidLazyCommandRule>
 */
final class InvalidLazyCommandRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new InvalidLazyCommandRule();
	}

	public function testLazyCommand(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleCommand.php',
			],
			[
				[
					'Symfony Commands should be lazy. See https://symfony.com/blog/new-in-symfony-3-4-lazy-commands',
					16,
				],
			]
		);
	}

}
