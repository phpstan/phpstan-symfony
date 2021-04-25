<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$serializer = new \Symfony\Component\Serializer\Serializer();

assertType('Bar', $serializer->deserialize('bar', 'Bar', 'format'));
assertType('array<Bar>', $serializer->deserialize('bar', 'Bar[]', 'format'));
assertType('array<array<Bar>>', $serializer->deserialize('bar', 'Bar[][]', 'format'));
assertType('mixed', $serializer->deserialize('bar'));
