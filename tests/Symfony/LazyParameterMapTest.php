<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPUnit\Framework\TestCase;

final class LazyParameterMapTest extends TestCase
{

	public function testFactoryIsNotCalledOnConstruction(): void
	{
		$factory = $this->createMock(ParameterMapFactory::class);
		$factory->expects(self::never())->method('create');

		LazyParameterMap::create($factory);
	}

	public function testDelegation(): void
	{
		$parameter = new Parameter('app.string', 'abcdef');
		$innerMap = new DefaultParameterMap(['app.string' => $parameter]);

		$factory = $this->createMock(ParameterMapFactory::class);
		$factory->expects(self::once())->method('create')->willReturn($innerMap);

		$lazyMap = LazyParameterMap::create($factory);

		self::assertSame($innerMap->getParameters(), $lazyMap->getParameters());
		self::assertSame($innerMap->getParameter('app.string'), $lazyMap->getParameter('app.string'));
		self::assertNull($lazyMap->getParameter('unknown'));

		$node = new Variable('x');
		$scope = $this->createMock(Scope::class);
		$scope->method('getType')->with($node)->willReturn(new ConstantStringType('app.string'));

		self::assertSame($innerMap::getParameterKeysFromNode($node, $scope), $lazyMap::getParameterKeysFromNode($node, $scope));
	}

}
