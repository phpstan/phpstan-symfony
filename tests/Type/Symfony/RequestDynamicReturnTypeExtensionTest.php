<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NeverType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;

final class RequestDynamicReturnTypeExtensionTest extends TestCase
{

	public function testImplementsDynamicMethodReturnTypeExtension(): void
	{
		self::assertInstanceOf(
			DynamicMethodReturnTypeExtension::class,
			new RequestDynamicReturnTypeExtension()
		);
	}

	public function testGetClass(): void
	{
		$extension = new RequestDynamicReturnTypeExtension();
		self::assertSame('Symfony\Component\HttpFoundation\Request', $extension->getClass());
	}

	public function testIsMethodSupported(): void
	{
		$methodGetContent = $this->createMock(MethodReflection::class);
		$methodGetContent->expects(self::once())->method('getName')->willReturn('getContent');

		$methodFoo = $this->createMock(MethodReflection::class);
		$methodFoo->expects(self::once())->method('getName')->willReturn('foo');

		$extension = new RequestDynamicReturnTypeExtension();
		self::assertTrue($extension->isMethodSupported($methodGetContent));
		self::assertFalse($extension->isMethodSupported($methodFoo));
	}

	/**
	 * @dataProvider getTypeFromMethodCallProvider
	 * @param MethodReflection $methodReflection
	 * @param MethodCall $methodCall
	 * @param Type $expectedType
	 * @param Scope $scope
	 */
	public function testGetTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Type $expectedType, Scope $scope): void
	{
		$extension = new RequestDynamicReturnTypeExtension();
		$type = $extension->getTypeFromMethodCall(
			$methodReflection,
			$methodCall,
			$scope
		);
		self::assertEquals($expectedType, $type);
	}

	/**
	 * @return mixed[]
	 */
	public function getTypeFromMethodCallProvider(): array
	{
		$scopeAsString = $this->createMock(Scope::class);
		$scopeAsString->expects(self::once())->method('getType')->willReturn(new ConstantBooleanType(false));

		$scopeAsResource = $this->createMock(Scope::class);
		$scopeAsResource->expects(self::once())->method('getType')->willReturn(new ConstantBooleanType(true));

		$scopeUnknown = $this->createMock(Scope::class);
		$scopeUnknown->expects(self::once())->method('getType')->willReturn($this->createMock(Type::class));

		$parametersAcceptorUnknown = $this->createMock(ParametersAcceptor::class);
		$parametersAcceptorUnknown->expects(self::once())->method('getReturnType')->willReturn(new NeverType());

		$methodReflectionUnknown = $this->createMock(MethodReflection::class);
		$methodReflectionUnknown->expects(self::once())->method('getVariants')->willReturn([$parametersAcceptorUnknown]);

		return [
			'noArgument' => [
				$this->createMock(MethodReflection::class),
				new MethodCall($this->createMock(Expr::class), ''),
				new StringType(),
				$this->createMock(Scope::class),
			],
			'asString' => [
				$this->createMock(MethodReflection::class),
				new MethodCall($this->createMock(Expr::class), '', [new Arg($this->createMock(Expr::class))]),
				new StringType(),
				$scopeAsString,
			],
			'asResource' => [
				$this->createMock(MethodReflection::class),
				new MethodCall($this->createMock(Expr::class), '', [new Arg($this->createMock(Expr::class))]),
				new ResourceType(),
				$scopeAsResource,
			],
			'unknown' => [
				$methodReflectionUnknown,
				new MethodCall($this->createMock(Expr::class), '', [new Arg($this->createMock(Expr::class))]),
				new NeverType(),
				$scopeUnknown,
			],
		];
	}

}
