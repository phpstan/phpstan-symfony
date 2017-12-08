<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Type;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \Lookyman\PHPStan\Symfony\Type\ContainerInterfaceDynamicReturnTypeExtension
 */
final class ContainerInterfaceDynamicReturnTypeExtensionTest extends TestCase
{

	public function testImplementsDynamicMethodReturnTypeExtension()
	{
		self::assertInstanceOf(
			DynamicMethodReturnTypeExtension::class,
			new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../container.xml'))
		);
	}

	public function testGetClass()
	{
		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../container.xml'));
		self::assertEquals(ContainerInterface::class, $extension->getClass());
	}

	public function testIsMethodSupported()
	{
		$methodGet = $this->createMock(MethodReflection::class);
		$methodGet->expects(self::once())->method('getName')->willReturn('get');

		$methodFoo = $this->createMock(MethodReflection::class);
		$methodFoo->expects(self::once())->method('getName')->willReturn('foo');

		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../container.xml'));
		self::assertTrue($extension->isMethodSupported($methodGet));
		self::assertFalse($extension->isMethodSupported($methodFoo));
	}

	/**
	 * @dataProvider getTypeFromMethodCallProvider
	 */
	public function testGetTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Type $expectedType)
	{
		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../container.xml'));
		$type = $extension->getTypeFromMethodCall(
			$methodReflection,
			$methodCall,
			$this->createMock(Scope::class)
		);
		self::assertEquals($expectedType, $type);
	}

	public function getTypeFromMethodCallProvider(): array
	{
		$notFoundType = $this->createMock(Type::class);

		$methodReflectionNotFound = $this->createMock(MethodReflection::class);
		$methodReflectionNotFound->expects(self::once())->method('getReturnType')->willReturn($notFoundType);

		return [
			'found' => [
				$this->createMock(MethodReflection::class),
				new MethodCall($this->createMock(Expr::class), '', [new Arg(new String_('withClass'))]),
				new ObjectType('Foo'),
			],
			'notFound' => [
				$methodReflectionNotFound,
				new MethodCall($this->createMock(Expr::class), ''),
				$notFoundType,
			],
		];
	}

}
