<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$bag = new \Symfony\Component\HttpFoundation\HeaderBag(['foo' => ['bar']]);

assertType('string|null', $bag->get('foo'));
assertType('string|null', $bag->get('foo', null));
assertType('string', $bag->get('foo', 'baz'));
assertType('string|null', $bag->get('foo', null, true));
assertType('string', $bag->get('foo', 'baz', true));
assertType('array<int, string>', $bag->get('foo', null, false));
assertType('array<int, string>', $bag->get('foo', 'baz', false));
