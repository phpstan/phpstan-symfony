<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Lookyman\PHPStan\Symfony\ServiceMap
 */
final class ServiceMapTest extends TestCase
{

	public function testGetServices()
	{
		$serviceMap = new ServiceMap(__DIR__ . '/container.xml');
		self::assertEquals(
			[
				'withoutClass' => [
					'class' => \null,
					'public' => \true,
					'synthetic' => \false,
				],
				'withClass' => [
					'class' => 'Foo',
					'public' => \true,
					'synthetic' => \false,
				],
				'withoutPublic' => [
					'class' => 'Foo',
					'public' => \true,
					'synthetic' => \false,
				],
				'publicNotFalse' => [
					'class' => 'Foo',
					'public' => \true,
					'synthetic' => \false,
				],
				'private' => [
					'class' => 'Foo',
					'public' => \false,
					'synthetic' => \false,
				],
				'synthetic' => [
					'class' => 'Foo',
					'public' => \true,
					'synthetic' => \true,
				],
				'alias' => [
					'class' => 'Foo',
					'public' => \true,
					'synthetic' => \false,
				],
			],
			$serviceMap->getServices()
		);
	}

}
