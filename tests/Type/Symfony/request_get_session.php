<?php declare(strict_types = 1);

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = doRequest();

$session1 = $request->getSession();
$session1;

if ($request->hasSession()) {
	$session2 = $request->getSession();
	$session2;
}

die;
