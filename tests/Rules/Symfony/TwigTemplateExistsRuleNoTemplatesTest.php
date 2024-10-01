<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\TwigEnvironmentResolver;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TwigTemplateExistsRule>
 */
final class TwigTemplateExistsRuleNoTemplatesTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new TwigTemplateExistsRule(new TwigEnvironmentResolver(null));
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleTwigController.php',
			],
			[]
		);
	}

}
