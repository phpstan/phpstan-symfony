<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Type\Type;

final class MessageMap
{

	/** @var array<class-string, Type> */
	private $messageMap;

	/** @param array<class-string, Type> $messageMap */
	public function __construct(array $messageMap)
	{
		$this->messageMap = $messageMap;
	}

	/** @param class-string $class */
	public function getTypeForClass(string $class): ?Type
	{
		return $this->messageMap[$class] ?? null;
	}

}
