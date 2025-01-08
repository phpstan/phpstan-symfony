<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Testing\PHPStanTestCase;
use function count;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;

final class SymfonyContainerResultCacheMetaExtensionTest extends PHPStanTestCase
{

	private static string $configFilePath;

	/**
	 * This test has to check if hash of the Symfony Container is correctly calculated,
	 * in order to do that we need to dynamically create a temporary configuration file
	 * because `PHPStanTestCase::getContainer()` caches container under key calculated from
	 * additional config files' paths, so we can't reuse the same config file between tests.
	 */
	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../extension.neon',
			self::$configFilePath,
		];
	}

	/**
	 * @param list<string> $sameHashXmlContents
	 *
	 * @dataProvider provideContainerHashIsCalculatedCorrectlyCases
	 */
	public function testContainerHashIsCalculatedCorrectly(
		array $sameHashXmlContents,
		string $invalidatingXmlContent
	): void
	{
		$hash = null;

		self::assertGreaterThan(0, count($sameHashXmlContents));

		foreach ($sameHashXmlContents as $xmlContent) {
			$currentHash = $this->calculateSymfonyContainerHash($xmlContent);

			if ($hash === null) {
				$hash = $currentHash;
			} else {
				self::assertSame($hash, $currentHash);
			}
		}

		self::assertNotSame($hash, $this->calculateSymfonyContainerHash($invalidatingXmlContent));
	}

	/**
	 * @return iterable<string, array{list<string>, string}>
	 */
	public static function provideContainerHashIsCalculatedCorrectlyCases(): iterable
	{
		yield 'service "class" changes' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" />
							<service id="Bar" class="Bar" />
						</services>
					</container>
					XML,
				// Swapping services order in XML file does not affect the calculated hash
				<<<'XML'
					<container>
						<services>
							<service id="Bar" class="Bar" />
							<service id="Foo" class="Foo" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" />
						<service id="Bar" class="BarAdapter" />
					</services>
				</container>
				XML,
		];

		yield 'service visibility changes' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" public="true" />
						</services>
					</container>
					XML,
				// Placement of XML attributes does not affect the calculated hash
				<<<'XML'
					<container>
						<services>
							<service id="Foo" public="true" class="Foo" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" public="false" />
					</services>
				</container>
				XML,
		];

		yield 'service syntheticity changes' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" synthetic="false" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" synthetic="true" />
					</services>
				</container>
				XML,
		];

		yield 'service alias changes' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" />
							<service id="Bar" class="Bar" />
							<service id="Baz" alias="Foo" />
						</services>
					</container>
					XML,
				<<<'XML'
					<container>
						<services>
							<service id="Baz" alias="Foo" />
							<service id="Foo" class="Foo" />
							<service id="Bar" class="Bar" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" />
						<service id="Bar" class="Bar" />
						<service id="Baz" alias="Bar" />
					</services>
				</container>
				XML,
		];

		yield 'new service added' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" />
						<service id="Bar" class="Bar" />
					</services>
				</container>
				XML,
		];

		yield 'service removed' => [
			[
				<<<'XML'
					<container>
						<services>
							<service id="Foo" class="Foo" />
							<service id="Bar" class="Bar" />
						</services>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<services>
						<service id="Foo" class="Foo" />
					</services>
				</container>
				XML,
		];

		yield 'parameter value changes' => [
			[
				<<<'XML'
					<container>
						<parameters>
							<parameter key="foo">foo</parameter>
							<parameter key="bar">bar</parameter>
						</parameters>
					</container>
					XML,
				// Swapping parameters order in XML file does not affect the calculated hash
				<<<'XML'
					<container>
						<parameters>
							<parameter key="bar">bar</parameter>
							<parameter key="foo">foo</parameter>
						</parameters>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<parameters>
						<parameter key="foo">foo</parameter>
						<parameter key="bar">buzz</parameter>
					</parameters>
				</container>
				XML,
		];

		yield 'new parameter added' => [
			[
				<<<'XML'
					<container>
						<parameters>
							<parameter key="foo">foo</parameter>
						</parameters>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<parameters>
						<parameter key="foo">foo</parameter>
						<parameter key="bar">bar</parameter>
					</parameters>
				</container>
				XML,
		];

		yield 'parameter removed' => [
			[
				<<<'XML'
					<container>
						<parameters>
							<parameter key="foo">foo</parameter>
							<parameter key="bar">bar</parameter>
						</parameters>
					</container>
					XML,
			],
			<<<'XML'
				<container>
					<parameters>
						<parameter key="foo">foo</parameter>
					</parameters>
				</container>
				XML,
		];
	}

	private function calculateSymfonyContainerHash(string $xmlContent): string
	{
		$symfonyContainerXmlPath = tempnam(sys_get_temp_dir(), 'phpstan-meta-extension-test-container-xml-');
		self::$configFilePath = tempnam(sys_get_temp_dir(), 'phpstan-meta-extension-test-config-') . '.neon';

		file_put_contents(
			self::$configFilePath,
			<<<NEON
				parameters:
					symfony:
						containerXmlPath: '$symfonyContainerXmlPath'
				NEON,
		);
		file_put_contents($symfonyContainerXmlPath, $xmlContent);

		$metaExtension = new SymfonyContainerResultCacheMetaExtension(
			self::getContainer()->getByType(ParameterMap::class),
			self::getContainer()->getByType(ServiceMap::class),
		);

		return $metaExtension->getHash();
	}

}
