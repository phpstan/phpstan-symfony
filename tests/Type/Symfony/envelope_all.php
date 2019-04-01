<?php declare(strict_types = 1);

$envelope = new \Symfony\Component\Messenger\Envelope(new stdClass());

$test1 = $envelope->all(\Symfony\Component\Messenger\Stamp\ReceivedStamp::class);
$test2 = $envelope->all(random_bytes(1));
$test3 = $envelope->all();

die;
