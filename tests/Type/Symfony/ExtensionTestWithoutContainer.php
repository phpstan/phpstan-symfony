<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;

class ExtensionTestWithoutContainer extends TypeInferenceTestCase
{

	/** @return mixed[] */
	public function dataFileAsserts(): iterable
	{
		if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleController.php');
		}

		if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleAbstractController.php');
		}
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
		$this->assertFileAsserts($assertType, $file, ...$args);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
		];
	}

}
