<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TwigTemplateExistsRule>
 */
final class TwigTemplateExistsRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TwigTemplateExistsRule([__DIR__ . '/templates']);
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleTwigController.php',
			],
			[
				[
					'Twig template "bar.html.twig" does not exist.',
					22,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					23,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					24,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					25,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					26,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					27,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					35,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					36,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					37,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					44,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					45,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					53,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					57,
				],
				[
					'Twig template "bar.html.twig" does not exist.',
					61,
				],
				[
					'Twig template "baz.html.twig" does not exist.',
					61,
				],
			]
		);
	}

}
