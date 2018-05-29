<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{

	public function testGetSet(): void
	{
		$service = new Service('foo', 'Bar', true, true, 'alias');
		self::assertSame('foo', $service->getId());
		self::assertSame('Bar', $service->getClass());
		self::assertTrue($service->isPublic());
		self::assertTrue($service->isSynthetic());
		self::assertSame('alias', $service->getAlias());
	}

}
