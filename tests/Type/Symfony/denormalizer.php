<?php declare(strict_types = 1);

$serializer = new \Symfony\Component\Serializer\Serializer();

$first = $serializer->denormalize('bar', 'Bar', 'format');
$second = $serializer->denormalize('bar', 'Bar[]', 'format');
$third = $serializer->denormalize('bar', 'Bar[][]', 'format');
$fourth = $serializer->denormalize('bar');

die;
