<?php

namespace UuidStub;

use Symfony\Component\Uid\Uuid;
use function PHPStan\Testing\assertType;

$uuid = new Uuid::v4();

assertType('non-empty-string&non-decimal-int-string', $uuid->toRfc4122());
assertType('non-empty-string&non-decimal-int-string', $uuid->toHex());
assertType('non-empty-string&non-decimal-int-string', $uuid->hash());
assertType('non-empty-string&non-decimal-int-string', $uuid->toString());
assertType('non-empty-string&non-decimal-int-string', $uuid->jsonSerialize());
