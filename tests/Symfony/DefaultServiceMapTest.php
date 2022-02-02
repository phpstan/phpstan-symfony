<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Iterator;
use PHPUnit\Framework\TestCase;

final class DefaultServiceMapTest extends TestCase
{

	/**
	 * @dataProvider getServiceProvider
	 */
	public function testGetService(string $id, callable $validator): void
	{
		$factory = new XmlServiceMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/container.xml']));
		$validator($factory->create()->getService($id));
	}

	public function testGetContainerEscapedPath(): void
	{
		$factory = new XmlServiceMapFactory(new Configuration(['containerXmlPath' => __DIR__ . '/containers/bugfix%2Fcontainer.xml']));
		$serviceMap = $factory->create();

		self::assertNotNull($serviceMap->getService('withClass'));
	}

	/**
	 * @return Iterator<mixed>
	 */
	public function getServiceProvider(): Iterator
	{
		yield [
			'unknown',
			static function (?Service $service): void {
				self::assertNull($service);
			},
		];
		yield [
			'withoutClass',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('withoutClass', $service->getId());
				self::assertNull($service->getClass());
				self::assertFalse($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'withClass',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('withClass', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertFalse($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'withoutPublic',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('withoutPublic', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertFalse($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'publicNotTrue',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('publicNotTrue', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertFalse($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'public',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('public', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertTrue($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'synthetic',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('synthetic', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertFalse($service->isPublic());
				self::assertTrue($service->isSynthetic());
				self::assertNull($service->getAlias());
			},
		];
		yield [
			'alias',
			static function (?Service $service): void {
				self::assertNotNull($service);
				self::assertSame('alias', $service->getId());
				self::assertSame('Foo', $service->getClass());
				self::assertFalse($service->isPublic());
				self::assertFalse($service->isSynthetic());
				self::assertSame('withClass', $service->getAlias());
			},
		];
	}

}
