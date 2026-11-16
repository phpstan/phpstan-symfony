<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Symfony\AutowireLocatorTypeExtension;
use function class_exists;

/** @extends RuleTestCase<ServiceLocatorServiceRule> */
class ServiceLocatorServiceRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ServiceLocatorServiceRule(self::getContainer()->getByType(AutowireLocatorTypeExtension::class));
	}

	public function testKeys(): void
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireLocator')) {
			self::markTestSkipped('AutowireLocator requires Symfony 6.4.');
		}
		$this->analyse([__DIR__ . '/../data/service-locator.php.stub'], [
			['Service "missing" is not declared in this service locator.', 22],
		]);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/service-locator.neon'];
	}

}
