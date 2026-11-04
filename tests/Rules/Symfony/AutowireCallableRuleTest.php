<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Symfony\AutowireCallableTypeResolver;
use function class_exists;

/** @extends RuleTestCase<AutowireCallableRule> */
class AutowireCallableRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new AutowireCallableRule(self::getContainer()->getByType(AutowireCallableTypeResolver::class));
	}

	public function testAttributes(): void
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireCallable')) {
			self::markTestSkipped('AutowireCallable requires Symfony 6.3.');
		}
		$this->analyse([__DIR__ . '/../data/autowire-callable.php'], [
			['Cannot autowire parameter $missing: Method AutowireCallableRules\\Formatter::missing() does not exist.', 38],
			['Cannot autowire parameter $hidden: Method AutowireCallableRules\\Formatter::hidden() is not public.', 40],
			['Cannot autowire parameter $static: Method AutowireCallableRules\\Formatter::format() is not static.', 42],
			['Cannot autowire parameter $wrong: Callable signature is incompatible with AutowireCallableRules\\WrongConverter::convert().', 44],
			['Cannot autowire parameter $multiple: Adapter interface AutowireCallableRules\\MultipleMethods must have exactly one method.', 46],
		]);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/../../../extension.neon'];
	}

}
