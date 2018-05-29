<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Testing\TestCase;

final class ServiceMapTest extends TestCase
{

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
		$typeSpecifier = $this->createTypeSpecifier($printer, $broker);
		$scope = new Scope($broker, $printer, $typeSpecifier, ScopeContext::create(''));

		self::assertSame('foo', ServiceMap::getServiceIdFromNode(new String_('foo'), $scope));
		self::assertSame('bar', ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name(ExampleController::class), 'BAR'), $scope));
		self::assertSame('foobar', ServiceMap::getServiceIdFromNode(new Concat(new String_('foo'), new ClassConstFetch(new Name(ExampleController::class), 'BAR')), $scope));

		$scope = $scope->enterClass($broker->getClass(ExampleController::class));
		self::assertSame('bar', ServiceMap::getServiceIdFromNode(new ClassConstFetch(new Name('static'), 'BAR'), $scope));
	}

}
