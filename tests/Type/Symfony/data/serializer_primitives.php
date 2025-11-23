<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$serializer = new \Symfony\Component\Serializer\Serializer();

assertType('int', $serializer->deserialize('...', 'int[]', 'json')[0] + 1);
assertType('string', $serializer->deserialize('...', 'string[]', 'json')[0] . '');
assertType('bool', !$serializer->deserialize('...', 'bool[]', 'json')[0]);
assertType('float', $serializer->deserialize('...', 'float[]', 'json')[0] + 1.0);
assertType('int', $serializer->deserialize('...', 'int[][]', 'json')[0][0] + 1);

