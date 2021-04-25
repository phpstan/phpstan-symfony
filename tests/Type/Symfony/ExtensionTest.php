<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;

class ExtensionTest extends TypeInferenceTestCase
{

	public function dataFileAsserts(): iterable
	{
		yield from $this->gatherAssertTypes(__DIR__ . '/envelope_all.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/header_bag_get.php');

		if (class_exists('Symfony\Component\HttpFoundation\InputBag')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/input_bag.php');
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/Config/tree_builder.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/ExampleBaseCommand.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/ExampleOptionCommand.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/kernel_interface.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/request_get_content.php');


		$ref = new ReflectionMethod(Request::class, 'getSession');
		$doc = (string) $ref->getDocComment();
		if (strpos($doc, '@return SessionInterface|null') !== false) {
			yield from $this->gatherAssertTypes(__DIR__ . '/request_get_session_null.php');
		} else {
			yield from $this->gatherAssertTypes(__DIR__ . '/request_get_session.php');
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/serializer.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/denormalizer.php');

		if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/ExampleController.php');
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
			__DIR__ . '/extension-test.neon',
		];
	}


}
