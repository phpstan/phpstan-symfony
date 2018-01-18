<?php
declare(strict_types=1);

namespace Lookyman\PHPStan\Symfony;

use PHPUnit\Framework\TestCase;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/**
 * @covers \Lookyman\PHPStan\Symfony\ServiceMap
 */
final class ServiceMapTest extends TestCase
{

	/**
	 * @dataProvider getServiceFromNodeProvider
	 */
	public function testGetServiceFromNode(array $service): void
	{
		$serviceMap = new ServiceMap(__DIR__ . '/container.xml');
		self::assertEquals($service, $serviceMap->getServiceFromNode(new String_($service['id'])));
	}

	/**
	 * @expectedException \Lookyman\PHPStan\Symfony\Exception\XmlContainerNotExistsException
	 */
	public function testFileNotExists(): void
	{
		new ServiceMap(__DIR__ . '/foo.xml');
	}

	public function getServiceFromNodeProvider(): array
	{
		return [
			[['id' => 'withoutClass', 'class' => \null, 'public' => \true, 'synthetic' => \false]],
			[['id' => 'withClass', 'class' => 'Foo', 'public' => \true, 'synthetic' => \false]],
			[['id' => 'withoutPublic', 'class' => 'Foo', 'public' => \true, 'synthetic' => \false]],
			[['id' => 'publicNotFalse', 'class' => 'Foo', 'public' => \true, 'synthetic' => \false]],
			[['id' => 'private', 'class' => 'Foo', 'public' => \false, 'synthetic' => \false]],
			[['id' => 'synthetic', 'class' => 'Foo', 'public' => \true, 'synthetic' => \true]],
			[['id' => 'alias', 'class' => 'Foo', 'public' => \true, 'synthetic' => \false]],
		];
	}

	public function testGetServiceIdFromNode(): void
	{
		self::assertEquals('foo', ServiceMap::getServiceIdFromNode(new String_('foo')));
		self::assertEquals('bar', ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('bar'), '')));
	}

}
