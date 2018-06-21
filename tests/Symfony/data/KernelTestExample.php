<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class KernelTestExample extends KernelTestCase
{

	public function someTest(): void
	{
		self::$container->get('private');
	}

}
