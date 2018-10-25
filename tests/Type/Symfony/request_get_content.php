<?php declare(strict_types = 1);

/** @var \Symfony\Component\HttpFoundation\Request $request */
$request = doFoo();

$content1 = $request->getContent();
$content2 = $request->getContent(false);
$content3 = $request->getContent(true);
$content4 = $request->getContent(doBar());

die;
