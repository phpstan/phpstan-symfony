<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Iterator;
use PHPUnit\Framework\TestCase;

final class DefaultUrlGeneratingRoutesMapTest extends TestCase
{

	/**
	 * @dataProvider hasRouteNameProvider
	 */
	public function testHasRouteName(string $name, callable $validator): void
	{
		$factory = new PhpUrlGeneratingRoutesMapFactory(new Configuration(['urlGeneratingRulesFile' => __DIR__ . '/url_generating_routes.php']));
		$validator($factory->create()->hasRouteName($name));
	}

	/**
	 * @return \Iterator<mixed>
	 */
	public function hasRouteNameProvider(): Iterator
	{
		yield [
			'unknown',
			function (bool $hasRoute): void {
				self::assertFalse($hasRoute);
			},
		];
		yield [
			'some.non.existing.route',
			function (bool $hasRoute): void {
				self::assertFalse($hasRoute);
			},
		];
		yield [
			'someRoute1',
			function (bool $hasRoute): void {
				self::assertTrue($hasRoute);
			},
		];
		yield [
			'someRoute2',
			function (bool $hasRoute): void {
				self::assertTrue($hasRoute);
			},
		];
		yield [
			'someRoute3',
			function (bool $hasRoute): void {
				self::assertTrue($hasRoute);
			},
		];
	}

}
