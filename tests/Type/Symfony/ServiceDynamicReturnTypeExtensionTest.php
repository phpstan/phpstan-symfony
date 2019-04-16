<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use PHPStan\Symfony\XmlServiceMapFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ServiceDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider servicesProvider
	 */
	public function testServices(string $expression, string $type, ?string $container): void
	{
		$this->processFile(
			__DIR__ . '/ExampleController.php',
			$expression,
			$type,
			new ServiceDynamicReturnTypeExtension(Controller::class, true, (new XmlServiceMapFactory($container))->create())
		);
	}

	public function servicesProvider(): Iterator
	{
		yield ['$service1', 'Foo', __DIR__ . '/container.xml'];
		yield ['$service2', 'object', __DIR__ . '/container.xml'];
		yield ['$service3', 'object', __DIR__ . '/container.xml'];
		yield ['$service4', 'object', __DIR__ . '/container.xml'];
		yield ['$has1', 'true', __DIR__ . '/container.xml'];
		yield ['$has2', 'false', __DIR__ . '/container.xml'];
		yield ['$has3', 'bool', __DIR__ . '/container.xml'];
		yield ['$has4', 'bool', __DIR__ . '/container.xml'];

		yield ['$service1', 'object', null];
		yield ['$service2', 'object', null];
		yield ['$service3', 'object', null];
		yield ['$service4', 'object', null];
		yield ['$has1', 'bool', null];
		yield ['$has2', 'bool', null];
		yield ['$has3', 'bool', null];
		yield ['$has4', 'bool', null];
	}

	/**
	 * @dataProvider constantHassersOffProvider
	 */
	public function testConstantHassersOff(string $expression, string $type, ?string $container): void
	{
		$this->processFile(
			__DIR__ . '/ExampleController.php',
			$expression,
			$type,
			new ServiceDynamicReturnTypeExtension(Controller::class, false, (new XmlServiceMapFactory($container))->create())
		);
	}

	public function constantHassersOffProvider(): Iterator
	{
		yield ['$has1', 'bool', __DIR__ . '/container.xml'];
		yield ['$has2', 'bool', __DIR__ . '/container.xml'];

		yield ['$has1', 'bool', null];
		yield ['$has2', 'bool', null];
	}

}
