<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$serializer = new \Symfony\Component\Serializer\Serializer();

assertType('Bar', $serializer->denormalize('bar', 'Bar', 'format'));
assertType('array<Bar>', $serializer->denormalize('bar', 'Bar[]', 'format'));
assertType('array<array<Bar>>', $serializer->denormalize('bar', 'Bar[][]', 'format'));
assertType('mixed', $serializer->denormalize('bar'));
