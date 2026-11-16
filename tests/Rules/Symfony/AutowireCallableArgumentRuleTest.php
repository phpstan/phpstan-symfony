<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Symfony\AutowireCallableTypeExtension;
use PHPStan\Type\Symfony\AutowireCallableTypeResolver;
use function class_exists;

/** @extends RuleTestCase<AutowireCallableArgumentRule> */
class AutowireCallableArgumentRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new AutowireCallableArgumentRule(self::getContainer()->getByType(AutowireCallableTypeResolver::class), self::createReflectionProvider(), self::getContainer()->getByType(AutowireCallableTypeExtension::class));
	}

	public function testArguments(): void
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireCallable')) {
			self::markTestSkipped('AutowireCallable requires Symfony 6.3.');
		}
		$this->analyse([__DIR__ . '/../data/autowire-callable-arguments.php.stub'], [
			['Parameter $format expects the signature declared by its autowiring attribute.', 26],
			['Parameter $format expects the signature declared by its autowiring attribute.', 27],
			['Property $format expects the signature declared by its autowiring attribute.', 42],
		]);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/autowire-callable-arguments.neon'];
	}

}
