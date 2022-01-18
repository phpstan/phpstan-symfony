<?php declare(strict_types=1);

namespace PHPStan\Type\Symfony;

use Bar;
use stdClass;
use Symfony\Contracts\Cache\CacheInterface;
use function PHPStan\Testing\assertType;

/** @var CacheInterface $cacheInterface */
$cacheInterface = new stdClass();

$callbackCallable = static function (Bar $bar) {
	return $bar;
};

assertType('Bar', $cacheInterface->get('cacheTest', $callbackCallable));
