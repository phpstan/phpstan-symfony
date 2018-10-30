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
	public function testServices(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/ExampleController.php',
			$expression,
			$type,
			new ServiceDynamicReturnTypeExtension(Controller::class, true, (new XmlServiceMapFactory(__DIR__ . '/container.xml'))->create())
		);
	}

	public function servicesProvider(): Iterator
	{
		yield ['$service1', 'Foo'];
		yield ['$service2', 'object'];
		yield ['$service3', 'object'];
		yield ['$service4', 'object'];
		yield ['$has1', 'true'];
		yield ['$has2', 'false'];
		yield ['$has3', 'bool'];
		yield ['$has4', 'bool'];
	}

	/**
	 * @dataProvider constantHassersOffProvider
	 */
	public function testConstantHassersOff(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/ExampleController.php',
			$expression,
			$type,
			new ServiceDynamicReturnTypeExtension(Controller::class, false, (new XmlServiceMapFactory(__DIR__ . '/container.xml'))->create())
		);
	}

	public function constantHassersOffProvider(): Iterator
	{
		yield ['$has1', 'bool'];
		yield ['$has2', 'bool'];
	}

}
