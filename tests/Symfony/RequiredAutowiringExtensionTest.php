<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\AdditionalConstructorsExtension;
use PHPStan\Rules\Properties\UninitializedPropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Symfony\Contracts\Service\Attribute\Required;
use function class_exists;

/**
 * @extends RuleTestCase<UninitializedPropertyRule>
 */
final class RequiredAutowiringExtensionTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		$container = self::getContainer();
		$container->getServicesByTag(AdditionalConstructorsExtension::EXTENSION_TAG);

		return $container->getByType(UninitializedPropertyRule::class);
	}

	public function testRequiredAnnotations(): void
	{
		$this->analyse([__DIR__ . '/data/required-annotations.php'], [
			[
				'Class RequiredAnnotationTest\TestAnnotations has an uninitialized property $three. Give it default value or assign it in the constructor.',
				12,
			],
			[
				'Class RequiredAnnotationTest\TestAnnotations has an uninitialized property $four. Give it default value or assign it in the constructor.',
				14,
			],
		]);
	}

	public function testRequiredAttributes(): void
	{
		if (!class_exists(Required::class)) {
			self::markTestSkipped('Required symfony/service-contracts@3.2.1 or higher is not installed');
		}

		$this->analyse([__DIR__ . '/data/required-attributes.php'], [
			[
				'Class RequiredAttributesTest\TestAttributes has an uninitialized property $three. Give it default value or assign it in the constructor.',
				14,
			],
			[
				'Class RequiredAttributesTest\TestAttributes has an uninitialized property $four. Give it default value or assign it in the constructor.',
				16,
			],
		]);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/required-autowiring-config.neon',
		];
	}

}
