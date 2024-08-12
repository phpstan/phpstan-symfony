<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TwigTemplateExistsRule>
 */
final class TwigTemplateExistsRuleMoreTemplatesTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TwigTemplateExistsRule([
			__DIR__ . '/data',
			__DIR__ . '/templates',
		]);
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleTwigController.php',
			],
			[
				[
					'Twig template "baz.html.twig" does not exist.',
					61,
				],
			]
		);
	}

}
