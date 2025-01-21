<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Testing\PHPStanTestCase;
use function count;

/**
 * @phpstan-type ContainerContents array{parameters?: ParameterMap, services?: ServiceMap}
 */
final class SymfonyContainerResultCacheMetaExtensionTest extends PHPStanTestCase
{

	/**
	 * @param list<ContainerContents> $sameHashContents
	 * @param ContainerContents $invalidatingContent
	 *
	 * @dataProvider provideContainerHashIsCalculatedCorrectlyCases
	 */
	public function testContainerHashIsCalculatedCorrectly(
		array $sameHashContents,
		array $invalidatingContent
	): void
	{
		$hash = null;

		self::assertGreaterThan(0, count($sameHashContents));

		foreach ($sameHashContents as $content) {
			$currentHash = (new SymfonyContainerResultCacheMetaExtension(
				$content['parameters'] ?? new DefaultParameterMap([]),
				$content['services'] ?? new DefaultServiceMap([]),
			))->getHash();

			if ($hash === null) {
				$hash = $currentHash;
			} else {
				self::assertSame($hash, $currentHash);
			}
		}

		self::assertNotSame(
			$hash,
			(new SymfonyContainerResultCacheMetaExtension(
				$invalidatingContent['parameters'] ?? new DefaultParameterMap([]),
				$invalidatingContent['services'] ?? new DefaultServiceMap([]),
			))->getHash(),
		);
	}

	/**
	 * @return iterable<string, array{list<ContainerContents>, ContainerContents}>
	 */
	public static function provideContainerHashIsCalculatedCorrectlyCases(): iterable
	{
		yield 'service "class" changes' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
						new Service('Bar', 'Bar', true, false, null),
					]),
				],
				// Swapping services order in XML file does not affect the calculated hash
				[
					'services' => new DefaultServiceMap([
						new Service('Bar', 'Bar', true, false, null),
						new Service('Foo', 'Foo', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null),
					new Service('Bar', 'BarAdapter', true, false, null),
				]),
			],
		];

		yield 'service visibility changes' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', false, false, null),
				]),
			],
		];

		yield 'service syntheticity changes' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, true, null),
				]),
			],
		];

		yield 'service alias changes' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
						new Service('Bar', 'Bar', true, false, null),
						new Service('Baz', null, true, false, 'Foo'),
					]),
				],
				// Swapping services order in XML file does not affect the calculated hash
				[
					'services' => new DefaultServiceMap([
						new Service('Baz', null, true, false, 'Foo'),
						new Service('Bar', 'Bar', true, false, null),
						new Service('Foo', 'Foo', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null),
					new Service('Bar', 'Bar', true, false, null),
					new Service('Baz', null, true, false, 'Bar'),
				]),
			],
		];

		yield 'service tag attributes changes' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null, [
							new ServiceTag('foo.bar', ['baz' => 'bar']),
							new ServiceTag('foo.baz', ['baz' => 'baz']),
						]),
					]),
				],
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null, [
							new ServiceTag('foo.baz', ['baz' => 'baz']),
							new ServiceTag('foo.bar', ['baz' => 'bar']),
						]),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null, [
						new ServiceTag('foo.bar', ['baz' => 'bar']),
						new ServiceTag('foo.baz', ['baz' => 'buzz']),
					]),
				]),
			],
		];

		yield 'service tag added' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null, [
							new ServiceTag('foo.bar', ['baz' => 'bar']),
						]),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null, [
						new ServiceTag('foo.bar', ['baz' => 'bar']),
						new ServiceTag('foo.baz', ['baz' => 'baz']),
					]),
				]),
			],
		];

		yield 'service tag removed' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null, [
							new ServiceTag('foo.bar', ['baz' => 'bar']),
							new ServiceTag('foo.baz', ['baz' => 'baz']),
						]),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null, [
						new ServiceTag('foo.bar', ['baz' => 'bar']),
					]),
				]),
			],
		];

		yield 'new service added' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null),
					new Service('Bar', 'Bar', true, false, null),
				]),
			],
		];

		yield 'service removed' => [
			[
				[
					'services' => new DefaultServiceMap([
						new Service('Foo', 'Foo', true, false, null),
						new Service('Bar', 'Bar', true, false, null),
					]),
				],
			],
			[
				'services' => new DefaultServiceMap([
					new Service('Foo', 'Foo', true, false, null),
				]),
			],
		];

		yield 'parameter value changes' => [
			[
				[
					'parameters' => new DefaultParameterMap([
						new Parameter('foo', 'foo'),
						new Parameter('bar', 'bar'),
					]),
				],
				[
					'parameters' => new DefaultParameterMap([
						new Parameter('bar', 'bar'),
						new Parameter('foo', 'foo'),
					]),
				],
			],
			[
				'parameters' => new DefaultParameterMap([
					new Parameter('foo', 'foo'),
					new Parameter('bar', 'buzz'),
				]),
			],
		];

		yield 'new parameter added' => [
			[
				[
					'parameters' => new DefaultParameterMap([
						new Parameter('foo', 'foo'),
					]),
				],
			],
			[
				'parameters' => new DefaultParameterMap([
					new Parameter('foo', 'foo'),
					new Parameter('bar', 'bar'),
				]),
			],
		];

		yield 'parameter removed' => [
			[
				[
					'parameters' => new DefaultParameterMap([
						new Parameter('foo', 'foo'),
						new Parameter('bar', 'bar'),
					]),
				],
			],
			[
				'parameters' => new DefaultParameterMap([
					new Parameter('foo', 'foo'),
				]),
			],
		];
	}

}
