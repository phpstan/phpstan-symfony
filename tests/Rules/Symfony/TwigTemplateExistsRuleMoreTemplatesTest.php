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
			__DIR__ . '/twig/templates' => null,
			__DIR__ . '/twig/admin' => 'admin',
			__DIR__ . '/twig/user' => null,
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
				[
					'Twig template "@admin/foo.html.twig" does not exist.',
					66,
				],
				[
					'Twig template "backend.html.twig" does not exist.',
					67,
				],
			]
		);
	}

}
