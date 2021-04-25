<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;

class ExtensionTestWithoutContainer extends TypeInferenceTestCase
{

	public function dataFileAsserts(): iterable
	{
		yield from $this->gatherAssertTypes(__DIR__ . '/ExampleControllerWithoutContainer.php');
	}

	/**
	 * @dataProvider dataFileAsserts
	 * @param string $assertType
	 * @param string $file
	 * @param mixed ...$args
	 */
	public function testFileAsserts(
		string $assertType,
		string $file,
		...$args
	): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			$this->markTestSkipped('Needs class Symfony\Bundle\FrameworkBundle\Controller\Controller');
		}
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
		];
	}
}
