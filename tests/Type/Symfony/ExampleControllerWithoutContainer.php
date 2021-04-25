<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ExampleControllerWithoutContainer extends Controller
{

	public function services(): void
	{
		assertType('object', $this->get('foo'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('bool', $this->has('foo'));
		assertType('bool', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

}
