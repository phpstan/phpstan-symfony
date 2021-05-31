<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$bag = new \Symfony\Component\HttpFoundation\InputBag(['foo' => 'bar', 'bar' => ['x']]);

assertType('bool|float|int|string|null', $bag->get('foo'));
assertType('bool|float|int|string|null', $bag->get('foo', null));
assertType('bool|float|int|string', $bag->get('foo', ''));
assertType('bool|float|int|string', $bag->get('foo', 'baz'));
assertType('array<string, array<bool|float|int|string>|bool|float|int|string>', $bag->all());
assertType('array<bool|float|int|string>', $bag->all('bar'));
