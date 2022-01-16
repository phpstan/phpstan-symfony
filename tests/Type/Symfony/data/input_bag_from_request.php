<?php

namespace InputBag;

use Symfony\Component\HttpFoundation\Request;
use function PHPStan\Testing\assertType;

class Foo
{

	public function doFoo(Request $request): void
	{
		assertType('string|null', $request->request->get('foo'));
		assertType('string|null', $request->query->get('foo'));
		assertType('string|null', $request->cookies->get('foo'));

		assertType('string', $request->request->get('foo', 'foo'));
		assertType('string', $request->query->get('foo', 'foo'));
		assertType('string', $request->cookies->get('foo', 'foo'));
	}

}
