<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function PHPStan\Testing\assertType;

final class ExampleAbstractControllerServices extends AbstractController
{

	public function services(): void
	{
		assertType('Foo', $this->get('foo'));
		assertType('Foo', $this->get('parameterised_foo'));
		assertType('Foo\Bar', $this->get('parameterised_bar'));
		assertType('Synthetic', $this->get('synthetic'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('true', $this->has('foo'));
		assertType('true', $this->has('synthetic'));
		assertType('false', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

}
