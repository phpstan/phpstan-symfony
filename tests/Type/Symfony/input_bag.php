<?php declare(strict_types = 1);

$bag = new \Symfony\Component\HttpFoundation\InputBag(['foo' => 'bar', 'bar' => ['x']]);

$test1 = $bag->get('foo');
$test2 = $bag->get('foo', null);
$test3 = $bag->get('foo', '');
$test4 = $bag->get('foo', 'baz');
$test5 = $bag->all();
$test6 = $bag->all('bar');

die;
