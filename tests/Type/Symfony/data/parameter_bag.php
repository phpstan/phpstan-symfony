<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$bag = new \Symfony\Component\HttpFoundation\ParameterBag(['foo' => 'bar']);

assertType('list<(int|string)>', $bag->keys());
assertType('array<mixed>', $bag->all());
assertType('array<mixed>', $bag->all('foo'));

foreach ($bag as $key => $value) {
	assertType('(int|string)', $key);
}
