<?php
declare(strict_types=1);

namespace Lookyman\PHPStan\Symfony;

use Lookyman\PHPStan\Symfony\Rules\data\ExampleController;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Testing\TestCase;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;

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
		$printer = new Standard();

		$serviceMap = new ServiceMap(__DIR__ . '/container.xml');
		self::assertSame($service, $serviceMap->getServiceFromNode(
			new String_($service['id']),
			new Scope($this->createBroker(), $printer, new TypeSpecifier($printer), '')
		));
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
		$broker = $this->createBroker();
		$printer = new Standard();
		$scope = new Scope($broker, $printer, new TypeSpecifier($printer), '');

		self::assertSame('foo', ServiceMap::getServiceIdFromNode(new String_('foo'), $scope));
		self::assertSame('bar', ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('bar'), ''), $scope));
		self::assertSame('foobar', ServiceMap::getServiceIdFromNode(new Concat(new String_('foo'), new ClassConstFetch(new Name('bar'), '')), $scope));

		$scope = $scope->enterClass($broker->getClass(ExampleController::class));
		self::assertEquals(ExampleController::class, ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('static'), ExampleController::class), $scope));
	}

}
