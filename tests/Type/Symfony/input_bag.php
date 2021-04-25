<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$bag = new \Symfony\Component\HttpFoundation\InputBag(['foo' => 'bar', 'bar' => ['x']]);

assertType('string|null', $bag->get('foo'));
assertType('string|null', $bag->get('foo', null));
assertType('string', $bag->get('foo', ''));
assertType('string', $bag->get('foo', 'baz'));
assertType('array<string, array<string>|string>', $bag->all());
assertType('array<string>', $bag->all('bar'));
