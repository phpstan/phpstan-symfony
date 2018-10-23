<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPUnit\Framework\TestCase;

final class ControllerDynamicReturnTypeExtensionTest extends TestCase
{

	public function testImplementsDynamicMethodReturnTypeExtension(): void
	{
		self::assertInstanceOf(
			DynamicMethodReturnTypeExtension::class,
			new ControllerDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'))
		);
	}

	public function testGetClass(): void
	{
		$extension = new ControllerDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		self::assertSame('Symfony\Bundle\FrameworkBundle\Controller\Controller', $extension->getClass());
	}

	public function testIsMethodSupported(): void
	{
		$methodGet = $this->createMock(MethodReflection::class);
		$methodGet->expects(self::once())->method('getName')->willReturn('get');

		$methodHas = $this->createMock(MethodReflection::class);
		$methodHas->expects(self::once())->method('getName')->willReturn('has');

		$methodCreateForm = $this->createMock(MethodReflection::class);
		$methodCreateForm->expects(self::once())->method('getName')->willReturn('createForm');

		$methodFoo = $this->createMock(MethodReflection::class);
		$methodFoo->expects(self::once())->method('getName')->willReturn('foo');

		$extension = new ControllerDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		self::assertTrue($extension->isMethodSupported($methodGet));
		self::assertTrue($extension->isMethodSupported($methodHas));
		self::assertTrue($extension->isMethodSupported($methodCreateForm));
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
		$extension = new ControllerDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
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
		$foundType = new ObjectType('Foo');
		$parametersAcceptorFound = $this->createMock(ParametersAcceptor::class);
		$parametersAcceptorFound->expects(self::once())->method('getReturnType')->willReturn($foundType);
		$methodReflectionFound = $this->createMock(MethodReflection::class);
		$methodReflectionFound->expects(self::once())->method('getName')->willReturn('get');
		$methodReflectionFound->expects(self::once())->method('getVariants')->willReturn([$parametersAcceptorFound]);
		$scopeFound = $this->createMock(Scope::class);
		$scopeFound->expects(self::once())->method('getType')->willReturn(new ConstantStringType('withClass'));

		$notFoundType = $this->createMock(Type::class);
		$parametersAcceptorNotFound = $this->createMock(ParametersAcceptor::class);
		$parametersAcceptorNotFound->expects(self::once())->method('getReturnType')->willReturn($notFoundType);
		$methodReflectionNotFound = $this->createMock(MethodReflection::class);
		$methodReflectionNotFound->expects(self::once())->method('getName')->willReturn('get');
		$methodReflectionNotFound->expects(self::once())->method('getVariants')->willReturn([$parametersAcceptorNotFound]);
		$scopeNotFound = $this->createMock(Scope::class);
		$scopeNotFound->expects(self::once())->method('getType')->willReturn(new ErrorType());

		return [
			'found' => [
				$methodReflectionFound,
				new MethodCall($this->createMock(Expr::class), 'someMethod', [new Arg(new String_('withClass'))]),
				new ObjectType('Foo'),
				$scopeFound,
			],
			'notFound' => [
				$methodReflectionNotFound,
				new MethodCall($this->createMock(Expr::class), 'someMethod', [new Arg(new String_('foobarbaz'))]),
				$notFoundType,
				$scopeNotFound,
			],
		];
	}

	public function testCreateContainer(): void
	{
		$parametersAcceptorFound = $this->createMock(ParametersAcceptor::class);
		$parametersAcceptorFound->expects(self::once())->method('getReturnType')->willReturn(new ObjectType('Symfony\Component\Form\FormInterface'));

		$methodReflection = $this->createMock(MethodReflection::class);
		$methodReflection->expects(self::once())->method('getName')->willReturn('createForm');
		$methodReflection->expects(self::once())->method('getVariants')->willReturn([$parametersAcceptorFound]);

		$methodCall = new MethodCall($this->createMock(Expr::class), 'createForm', [new Arg(new String_('PHPStan\Symfony\ExampleFormType'))]);

		$scope = $this->createMock(Scope::class);
		$scope->expects(self::once())->method('getType')->willReturn(new ConstantStringType('PHPStan\Symfony\ExampleFormType'));

		$thisClassReflection = $this->createMock(ClassReflection::class);
		$thisClassReflection->expects(self::once())->method('getName')->willReturn('Symfony\Component\Form\FormInterface');

		$thatClassReflection = $this->createMock(ClassReflection::class);
		$thatClassReflection->expects(self::once())->method('getName')->willReturn('PHPStan\Symfony\ExampleFormType');
		$thatClassReflection->expects(self::once())->method('isSubclassOf')->willReturn(true);

		$broker = $this->createMock(Broker::class);
		$broker->expects(self::at(0))->method('hasClass')->willReturn(true);
		$broker->expects(self::at(1))->method('hasClass')->willReturn(true);
		$broker->expects(self::at(2))->method('getClass')->willReturn($thisClassReflection);
		$broker->expects(self::at(3))->method('getClass')->willReturn($thatClassReflection);
		Broker::registerInstance($broker);

		$extension = new ControllerDynamicReturnTypeExtension(new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml'));
		$type = $extension->getTypeFromMethodCall(
			$methodReflection,
			$methodCall,
			$scope
		);

		self::assertInstanceOf(ObjectType::class, $type);
		self::assertSame('PHPStan\Symfony\ExampleFormType', $type->getClassName());
	}

}
