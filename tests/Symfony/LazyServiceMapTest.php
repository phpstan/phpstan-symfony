<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPUnit\Framework\TestCase;

final class LazyServiceMapTest extends TestCase
{

	public function testFactoryIsNotCalledOnConstruction(): void
	{
		$factory = $this->createMock(ServiceMapFactory::class);
		$factory->expects(self::never())->method('create');

		LazyServiceMap::create($factory);
	}

	public function testDelegation(): void
	{
		$service = new Service('withClass', 'Foo', false, false, null);
		$innerMap = new DefaultServiceMap(['withClass' => $service]);

		$factory = $this->createMock(ServiceMapFactory::class);
		$factory->expects(self::once())->method('create')->willReturn($innerMap);

		$lazyMap = LazyServiceMap::create($factory);

		self::assertSame($innerMap->getServices(), $lazyMap->getServices());
		self::assertSame($innerMap->getService('withClass'), $lazyMap->getService('withClass'));
		self::assertNull($lazyMap->getService('unknown'));

		$node = new Variable('x');
		$scope = $this->createMock(Scope::class);
		$scope->method('getType')->with($node)->willReturn(new ConstantStringType('withClass'));

		self::assertSame($innerMap::getServiceIdFromNode($node, $scope), $lazyMap::getServiceIdFromNode($node, $scope));
	}

}
