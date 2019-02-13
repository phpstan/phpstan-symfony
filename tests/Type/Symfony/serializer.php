<?php declare(strict_types = 1);

$serializer = new \Symfony\Component\Serializer\Serializer();

$first = $serializer->deserialize('bar', 'Bar', 'format');
$second = $serializer->deserialize('bar', 'Bar[]', 'format');
$third = $serializer->deserialize('bar', 'Bar[][]', 'format');

die;
