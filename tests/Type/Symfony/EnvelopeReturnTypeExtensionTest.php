<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class EnvelopeReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testAll(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/envelope_all.php',
			$expression,
			$type,
			new EnvelopeReturnTypeExtension()
		);
	}

	/**
	 * @return \Iterator<mixed>
	 */
	public function getProvider(): Iterator
	{
		yield ['$test1', 'array<' . ReceivedStamp::class . '>'];
		yield ['$test2', 'array<' . StampInterface::class . '>'];
		yield ['$test3', 'array<array<' . StampInterface::class . '>>'];
	}

}
