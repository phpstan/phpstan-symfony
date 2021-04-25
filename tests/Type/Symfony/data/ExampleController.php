<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use function PHPStan\Testing\assertType;

final class ExampleController extends Controller
{

	public function services(): void
	{
		assertType('Foo', $this->get('foo'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('true', $this->has('foo'));
		assertType('false', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

}
