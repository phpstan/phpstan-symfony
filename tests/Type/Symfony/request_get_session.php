<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\HttpFoundation\Request;

class Foo {

	public function doFoo($session1, $session2): void
	{
		/** @var Request $request */
		$request = doRequest();

		$session1 = $request->getSession();
		$session1;

		if ($request->hasSession()) {
			$session2 = $request->getSession();
			$session2;
		}
	}

}
