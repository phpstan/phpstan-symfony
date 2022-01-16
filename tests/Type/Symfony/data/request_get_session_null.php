<?php declare(strict_types = 1);

use Symfony\Component\HttpFoundation\Session\Session;
use function PHPStan\Testing\assertType;

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = doRequest();

$session1 = $request->getSession();
assertType(Session::class . '|null', $request->getSession());

if ($request->hasSession()) {
	assertType(Session::class, $request->getSession());
}
