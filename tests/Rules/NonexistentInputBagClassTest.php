<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CallMethodsRule>
 */
class NonexistentInputBagClassTest extends RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return self::getContainer()->getByType(CallMethodsRule::class);
	}

	public function testInputBag(): void
	{
		$this->analyse([__DIR__ . '/data/input_bag.php'], []);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../extension.neon',
			__DIR__ . '/../../rules.neon',
		];
	}

}
