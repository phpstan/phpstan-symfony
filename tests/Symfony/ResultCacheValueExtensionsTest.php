<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;

final class ResultCacheValueExtensionsTest extends PHPStanTestCase
{

	public function testService(): void
	{
		$value = (new ServiceResultCacheValueExtension(self::services([
			new Service('Foo', 'Foo', true, false, null),
			new Service('Bar', 'Bar', true, false, null),
		]), self::parameters([])))->getValue('Foo');

		// what the other services are, and in which order, is not part of it
		self::assertSame($value, (new ServiceResultCacheValueExtension(self::services([
			new Service('Bar', 'BarAdapter', false, true, null),
			new Service('Baz', 'Baz', true, false, null),
			new Service('Foo', 'Foo', true, false, null, [new ServiceTag('foo.bar', ['baz' => 'bar'])]),
		]), self::parameters([new Parameter('foo', 'foo')])))->getValue('Foo'));

		foreach ([
			'class' => new Service('Foo', 'FooAdapter', true, false, null),
			'visibility' => new Service('Foo', 'Foo', false, false, null),
			'syntheticity' => new Service('Foo', 'Foo', true, true, null),
		] as $change => $service) {
			self::assertNotSame($value, (new ServiceResultCacheValueExtension(self::services([$service]), self::parameters([])))->getValue('Foo'), $change);
		}

		self::assertSame('missing', (new ServiceResultCacheValueExtension(self::services([]), self::parameters([])))->getValue('Foo'));
	}

	public function testServiceClassFromParameter(): void
	{
		$serviceMap = self::services([new Service('Foo', '%foo.class%', true, false, null)]);
		$value = (new ServiceResultCacheValueExtension($serviceMap, self::parameters([
			new Parameter('foo.class', 'Foo'),
		])))->getValue('Foo');

		self::assertNotSame($value, (new ServiceResultCacheValueExtension($serviceMap, self::parameters([
			new Parameter('foo.class', 'FooAdapter'),
		])))->getValue('Foo'));
	}

	public function testParameter(): void
	{
		$value = (new ParameterResultCacheValueExtension(self::parameters([
			new Parameter('foo', ['a' => 1]),
			new Parameter('bar', 'bar'),
		])))->getValue('foo');

		self::assertSame($value, (new ParameterResultCacheValueExtension(self::parameters([
			new Parameter('bar', 'buzz'),
			new Parameter('foo', ['a' => 1]),
		])))->getValue('foo'));
		self::assertNotSame($value, (new ParameterResultCacheValueExtension(self::parameters([
			new Parameter('foo', ['a' => 2]),
		])))->getValue('foo'));
		self::assertSame('missing', (new ParameterResultCacheValueExtension(self::parameters([])))->getValue('foo'));
	}

	public function testMessage(): void
	{
		$reflectionProvider = self::getContainer()->getByType(ReflectionProvider::class);
		$extension = new MessageResultCacheValueExtension(new MessageMapFactory(self::services([
			new Service('tagged_handler', 'MessengerHandleTrait\TaggedHandler', true, false, null, [
				new ServiceTag('messenger.message_handler', ['handles' => 'MessengerHandleTrait\TaggedQuery', 'method' => 'handle']),
			]),
		]), $reflectionProvider));

		self::assertSame('MessengerHandleTrait\TaggedResult', $extension->getValue('MessengerHandleTrait\TaggedQuery'));
		self::assertSame('none', $extension->getValue('MessengerHandleTrait\RegularQuery'));
	}

	/**
	 * @param list<Service> $services
	 */
	private static function services(array $services): DefaultServiceMap
	{
		$map = [];
		foreach ($services as $service) {
			$map[$service->getId()] = $service;
		}

		return new DefaultServiceMap($map);
	}

	/**
	 * @param list<Parameter> $parameters
	 */
	private static function parameters(array $parameters): DefaultParameterMap
	{
		$map = [];
		foreach ($parameters as $parameter) {
			$map[$parameter->getKey()] = $parameter;
		}

		return new DefaultParameterMap($map);
	}

}
