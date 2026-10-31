<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;
use function class_exists;
use function trait_exists;

class AutowireLocatorTypeExtensionTest extends TypeInferenceTestCase
{

	public function dataAsserts(): iterable
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireLocator')) {
			yield ['type', ''];
			return;
		}
		yield from $this->gatherAssertTypes(__DIR__ . '/data/autowire-locator.php.stub');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/subscribed-service-locator.php.stub');
		if (!trait_exists('Symfony\Contracts\Service\ServiceMethodsSubscriberTrait')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/subscribed-service-hooks.php.stub');
	}

	/**
	 * @dataProvider dataAsserts
	 * @param mixed ...$args
	 */
	public function testAsserts(string $assertType, string $file, ...$args): void
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireLocator')) {
			self::markTestSkipped('AutowireLocator requires Symfony 6.4.');
		}
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		$config = [__DIR__ . '/autowire-locator.neon'];
		if (trait_exists('Symfony\Contracts\Service\ServiceMethodsSubscriberTrait')) {
			$config[] = __DIR__ . '/autowire-locator-hooks.neon';
		}
		return $config;
	}

}
