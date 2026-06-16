<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Testing\PHPStanTestCase;
use function count;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function mkdir;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * @phpstan-type ContainerContents array{parameters?: ParameterMap, services?: ServiceMap}
 */
final class SymfonyContainerResultCacheMetaExtensionTest extends PHPStanTestCase
{

	private string $tmpDir;

	protected function setUp(): void
	{
		parent::setUp();
		$this->tmpDir = sys_get_temp_dir() . '/phpstan-symfony-test-' . uniqid('', true);
		mkdir($this->tmpDir, 0777, true);
	}

	protected function tearDown(): void
	{
		$cacheFiles = glob($this->tmpDir . '/*.hash');
		if ($cacheFiles !== false) {
			foreach ($cacheFiles as $file) {
				unlink($file);
			}
		}
		rmdir($this->tmpDir);
		parent::tearDown();
	}

	public function testHashIsCalculatedAndWrittenToCacheFileOnCacheMiss(): void
	{
		$containerXmlPath = __DIR__ . '/container.xml';

		$extension = new SymfonyContainerResultCacheMetaExtension(
			new DefaultParameterMap([]),
			new DefaultServiceMap([]),
			$this->tmpDir,
			$containerXmlPath,
		);

		$hash = $extension->getHash();

		$resultCacheHashFile = sprintf('%s/symfonyDiContainer-container.xml-result-cache-meta.hash', $this->tmpDir);
		self::assertFileExists($resultCacheHashFile);
		self::assertSame($hash, file_get_contents($resultCacheHashFile));

		$xmlHashFile = sprintf('%s/symfonyDiContainer-container.xml.hash', $this->tmpDir);
		self::assertFileExists($xmlHashFile);
		self::assertSame('c55d6ac45b535d6ecc9402cbb93825c38ec7b11b03f66577d0d3549b3d9ef75f', file_get_contents($xmlHashFile));
	}

	public function testCachedHashIsReturnedOnCacheHit(): void
	{
		$containerXmlPath = __DIR__ . '/container.xml';
		$xmlHashFile = sprintf('%s/symfonyDiContainer-container.xml.hash', $this->tmpDir);
		file_put_contents($xmlHashFile, 'c55d6ac45b535d6ecc9402cbb93825c38ec7b11b03f66577d0d3549b3d9ef75f');
		$resultCacheHashFile = sprintf('%s/symfonyDiContainer-container.xml-result-cache-meta.hash', $this->tmpDir);
		file_put_contents($resultCacheHashFile, 'pre-computed-hash');

		$extension = new SymfonyContainerResultCacheMetaExtension(
			new DefaultParameterMap([]),
			new DefaultServiceMap([]),
			$this->tmpDir,
			$containerXmlPath,
		);

		self::assertSame('pre-computed-hash', $extension->getHash());
	}

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
				__DIR__ . '/../../tmp',
				null,
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
				__DIR__ . '/../../tmp',
				null,
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
