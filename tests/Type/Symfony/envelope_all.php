<?php declare(strict_types = 1);

use function PHPStan\Testing\assertType;

$envelope = new \Symfony\Component\Messenger\Envelope(new stdClass());

assertType('array<Symfony\Component\Messenger\Stamp\ReceivedStamp>', $envelope->all(\Symfony\Component\Messenger\Stamp\ReceivedStamp::class));
assertType('array<Symfony\Component\Messenger\Stamp\StampInterface>', $envelope->all(random_bytes(1)));
assertType('array<array<Symfony\Component\Messenger\Stamp\StampInterface>>', $envelope->all());
