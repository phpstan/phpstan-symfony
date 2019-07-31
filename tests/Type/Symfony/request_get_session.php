<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\HttpFoundation\Request;

class Foo {

	public function doFoo(Request $request): void
	{
		$session1 = $request->getSession();
		if ($request->hasSession()) {
			$session2 = $request->getSession();
		}
	}

}
