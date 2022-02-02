<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;
use function class_exists;

class ExtensionTestWithoutContainer extends TypeInferenceTestCase
{

	/** @return mixed[] */
	public function dataExampleController(): iterable
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleController.php');
	}

	/** @return mixed[] */
	public function dataAbstractController(): iterable
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleAbstractController.php');
	}

	/**
	 * @dataProvider dataExampleController
	 * @dataProvider dataAbstractController
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
