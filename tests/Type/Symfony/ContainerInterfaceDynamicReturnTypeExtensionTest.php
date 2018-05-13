<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPStan\Type\Symfony\ContainerInterfaceDynamicReturnTypeExtension
 */
final class ContainerInterfaceDynamicReturnTypeExtensionTest extends TestCase
{

	public function testImplementsDynamicMethodReturnTypeExtension(): void
	{
		self::assertInstanceOf(
			DynamicMethodReturnTypeExtension::class,
			new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'))
		);
	}

	public function testGetClass(): void
	{
		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		self::assertSame('Symfony\Component\DependencyInjection\ContainerInterface', $extension->getClass());
	}

	public function testIsMethodSupported(): void
	{
		$methodGet = $this->createMock(MethodReflection::class);
		$methodGet->expects(self::once())->method('getName')->willReturn('get');

		$methodFoo = $this->createMock(MethodReflection::class);
		$methodFoo->expects(self::once())->method('getName')->willReturn('foo');

		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		self::assertTrue($extension->isMethodSupported($methodGet));
		self::assertFalse($extension->isMethodSupported($methodFoo));
	}

	/**
	 * @dataProvider getTypeFromMethodCallProvider
	 * @param MethodReflection $methodReflection
	 * @param MethodCall $methodCall
	 * @param Type $expectedType
	 */
	public function testGetTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Type $expectedType): void
	{
		$extension = new ContainerInterfaceDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		$type = $extension->getTypeFromMethodCall(
			$methodReflection,
			$methodCall,
			$this->createMock(Scope::class)
		);
		self::assertEquals($expectedType, $type);
	}

	/**
	 * @return mixed[]
	 */
	public function getTypeFromMethodCallProvider(): array
	{
		$notFoundType = $this->createMock(Type::class);

		$parametersAcceptorNotFound = $this->createMock(ParametersAcceptor::class);
		$parametersAcceptorNotFound->expects(self::once())->method('getReturnType')->willReturn($notFoundType);
		$methodReflectionNotFound = $this->createMock(MethodReflection::class);
		$methodReflectionNotFound->expects(self::once())->method('getVariants')->willReturn([$parametersAcceptorNotFound]);

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
