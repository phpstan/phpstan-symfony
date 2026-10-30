<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;
use function class_exists;

class AutowireCallableTypeExtensionTest extends TypeInferenceTestCase
{

	public function dataAsserts(): iterable
	{
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireCallable')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/autowire-callable.php.stub');
		if (!class_exists('Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/autowire-method-of.php.stub');
	}

	/**
	 * @dataProvider dataAsserts
	 * @param mixed ...$args
	 */
	public function testAsserts(string $assertType, string $file, ...$args): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [__DIR__ . '/autowire-callable.neon'];
	}

}
