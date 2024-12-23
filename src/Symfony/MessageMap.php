<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Type\Type;

final class MessageMap
{

	/** @var array<string, Type> */
	private $messageMap;

	/** @param array<string, Type> $messageMap */
	public function __construct(array $messageMap)
	{
		$this->messageMap = $messageMap;
	}

	public function getTypeForClass(string $class): ?Type
	{
		return $this->messageMap[$class] ?? null;
	}

}
