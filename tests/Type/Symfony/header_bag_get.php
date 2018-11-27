<?php declare(strict_types = 1);

$bag = new \Symfony\Component\HttpFoundation\HeaderBag(['foo' => ['bar']]);

$test1 = $bag->get('foo');
$test2 = $bag->get('foo', null);
$test3 = $bag->get('foo', 'baz');
$test4 = $bag->get('foo', ['baz']);

$test5 = $bag->get('foo', null, true);
$test6 = $bag->get('foo', 'baz', true);
$test7 = $bag->get('foo', ['baz'], true);

$test8 = $bag->get('foo', null, false);
$test9 = $bag->get('foo', 'baz', false);
$test10 = $bag->get('foo', ['baz'], false);

die;
