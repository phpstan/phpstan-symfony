<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Rules\Symfony\ExampleController;
use PHPStan\Testing\TestCase;

/**
 * @covers \PHPStan\Symfony\ServiceMap
 */
final class ServiceMapTest extends TestCase
{

	/**
	 * @dataProvider getServiceFromNodeProvider
	 * @param mixed[] $service
	 */
	public function testGetServiceFromNode(array $service): void
	{
		$broker = $this->createBroker();
		$printer = new Standard();

		$serviceMap = new ServiceMap(__DIR__ . '/data/container.xml');
		self::assertSame($service, $serviceMap->getServiceFromNode(
			new String_($service['id']),
			new Scope($broker, $printer, new TypeSpecifier($printer, $broker, [], [], []), ScopeContext::create(''))
		));
	}

	public function testFileNotExists(): void
	{
		$this->expectException(\PHPStan\Symfony\XmlContainerNotExistsException::class);
		new ServiceMap(__DIR__ . '/foo.xml');
	}

	/**
	 * @return mixed[]
	 */
	public function getServiceFromNodeProvider(): array
	{
		return [
			[['id' => 'withoutClass', 'class' => null, 'public' => true, 'synthetic' => false]],
			[['id' => 'withClass', 'class' => 'Foo', 'public' => true, 'synthetic' => false]],
			[['id' => 'withoutPublic', 'class' => 'Foo', 'public' => true, 'synthetic' => false]],
			[['id' => 'publicNotFalse', 'class' => 'Foo', 'public' => true, 'synthetic' => false]],
			[['id' => 'private', 'class' => 'Foo', 'public' => false, 'synthetic' => false]],
			[['id' => 'synthetic', 'class' => 'Foo', 'public' => true, 'synthetic' => true]],
			[['id' => 'alias', 'class' => 'Foo', 'public' => true, 'synthetic' => false]],
		];
	}

	public function testGetServiceIdFromNode(): void
	{
		$broker = $this->createBroker();
		$printer = new Standard();
		$scope = new Scope($broker, $printer, new TypeSpecifier($printer, $broker, [], [], []), ScopeContext::create(''));

		self::assertSame('foo', ServiceMap::getServiceIdFromNode(new String_('foo'), $scope));
		self::assertSame('bar', ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('bar'), ''), $scope));
		self::assertSame('foobar', ServiceMap::getServiceIdFromNode(new Concat(new String_('foo'), new ClassConstFetch(new Name('bar'), '')), $scope));

		$scope = $scope->enterClass($broker->getClass(ExampleController::class));
		self::assertSame(ExampleController::class, ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('static'), ExampleController::class), $scope));
	}

}
