<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ImpossibleCheckTypeMethodCallRule>
 */
class ImpossibleCheckTypeMethodCallRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return self::getContainer()->getByType(ImpossibleCheckTypeMethodCallRule::class);
	}

	public function testExtension(): void
	{
		$this->analyse([__DIR__ . '/data/request_get_session.php'], []);
	}

	public function testBug178(): void
	{
		$this->analyse([__DIR__ . '/data/bug-178.php'], []);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
			__DIR__ . '/../../../vendor/phpstan/phpstan-strict-rules/rules.neon',
		];
	}

}
