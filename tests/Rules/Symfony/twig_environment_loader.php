<?php declare(strict_types = 1);

use Twig\Environment;
use Twig\Loader\ArrayLoader;

require_once __DIR__ . '/../../../vendor/autoload.php';

$loader = new ArrayLoader(['foo.html.twig' => 'foo']);

return new Environment($loader);
