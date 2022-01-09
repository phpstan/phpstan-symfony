<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Iterator;
use PHPUnit\Framework\TestCase;

final class DefaultParameterMapTest extends TestCase
{

	/**
	 * @dataProvider getParameterProvider
	 */
	public function testGetParameter(string $key, callable $validator): void
	{
		$factory = new XmlParameterMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/container.xml']));
		$validator($factory->create()->getParameter($key));
	}

	public function testGetParameterEscapedPath(): void
	{
		$factory = new XmlParameterMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/containers/bugfix%2Fcontainer.xml']));
		$serviceMap = $factory->create();

		self::assertNotNull($serviceMap->getParameter('app.string'));
	}

	/**
	 * @return \Iterator<mixed>
	 */
	public function getParameterProvider(): Iterator
	{
		yield [
			'unknown',
			function (?Parameter $parameter): void {
				self::assertNull($parameter);
			},
		];
		yield [
			'app.string',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.string', $parameter->getKey());
				self::assertSame('abcdef', $parameter->getValue());
			},
		];
		yield [
			'app.int',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.int', $parameter->getKey());
				self::assertSame(123, $parameter->getValue());
			},
		];
		yield [
			'app.int_as_string',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.int_as_string', $parameter->getKey());
				self::assertSame('123', $parameter->getValue());
			},
		];
		yield [
			'app.float',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.float', $parameter->getKey());
				self::assertSame(123.45, $parameter->getValue());
			},
		];
		yield [
			'app.float_as_string',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.float_as_string', $parameter->getKey());
				self::assertSame('123.45', $parameter->getValue());
			},
		];
		yield [
			'app.boolean',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.boolean', $parameter->getKey());
				self::assertTrue($parameter->getValue());
			},
		];
		yield [
			'app.boolean_as_string',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.boolean_as_string', $parameter->getKey());
				self::assertSame('true', $parameter->getValue());
			},
		];
		yield [
			'app.list',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.list', $parameter->getKey());
				self::assertEquals(['en', 'es', 'fr'], $parameter->getValue());
			},
		];
		yield [
			'app.list_of_list',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.list_of_list', $parameter->getKey());
				self::assertEquals([
					['name' => 'the name', 'value' => 'the value'],
					['name' => 'another name', 'value' => 'another value'],
				], $parameter->getValue());
			},
		];
		yield [
			'app.map',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.map', $parameter->getKey());
				self::assertEquals([
					'a' => 'value of a',
					'b' => 'value of b',
					'c' => 'value of c',
				], $parameter->getValue());
			},
		];
		yield [
			'app.binary',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.binary', $parameter->getKey());
				self::assertSame('This is a Bell char ', $parameter->getValue());
			},
		];
		yield [
			'app.constant',
			function (?Parameter $parameter): void {
				self::assertNotNull($parameter);
				self::assertSame('app.constant', $parameter->getKey());
				self::assertSame('Y-m-d\TH:i:sP', $parameter->getValue());
			},
		];
	}

}
