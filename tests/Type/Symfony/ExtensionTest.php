<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Testing\TypeInferenceTestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use function class_exists;
use function strpos;

class ExtensionTest extends TypeInferenceTestCase
{

	/** @return mixed[] */
	public function dataFileAsserts(): iterable
	{
		yield from $this->gatherAssertTypes(__DIR__ . '/data/envelope_all.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/header_bag_get.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/response_header_bag_get_cookies.php');

		if (class_exists('Symfony\Component\HttpFoundation\InputBag')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/input_bag.php');
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/tree_builder.php');

		yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleBaseCommand.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleOptionCommand.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleOptionLazyCommand.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/kernel_interface.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/property_accessor.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/request_get_content.php');

		$ref = new ReflectionMethod(Request::class, 'getSession');
		$doc = (string) $ref->getDocComment();
		if (strpos($doc, '@return SessionInterface|null') !== false) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/request_get_session_null.php');
		} else {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/request_get_session.php');
		}

		if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleController.php');
		}

		if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/ExampleAbstractController.php');
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/serializer.php');

		if (class_exists('Symfony\Component\HttpFoundation\InputBag')) {
			yield from $this->gatherAssertTypes(__DIR__ . '/data/input_bag_from_request.php');
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/denormalizer.php');

		yield from $this->gatherAssertTypes(__DIR__ . '/data/FormInterface_getErrors.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/cache.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/form_data_type.php');

		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/with-configuration/WithConfigurationExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/without-configuration/WithoutConfigurationExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/anonymous/AnonymousExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/ignore-implemented/IgnoreImplementedExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/multiple-types/MultipleTypes.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/with-configuration-with-constructor/WithConfigurationWithConstructorExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/with-configuration-with-constructor-optional-params/WithConfigurationWithConstructorOptionalParamsExtension.php');
		yield from $this->gatherAssertTypes(__DIR__ . '/data/extension/with-configuration-with-constructor-required-params/WithConfigurationWithConstructorRequiredParamsExtension.php');

		if (!class_exists('Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator')) {
			return;
		}

		yield from $this->gatherAssertTypes(__DIR__ . '/data/definition_configurator.php');
	}

	/**
	 * @dataProvider dataFileAsserts
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
			'phar://' . __DIR__ . '/../../../vendor/phpstan/phpstan/phpstan.phar/conf/bleedingEdge.neon',
		];
	}

}
