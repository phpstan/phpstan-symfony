<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Type\Type;
use function count;

final class Message
{

	/** @var string */
	private $class;

	/** @var Type[] */
	private $returnTypes;

	/** @param Type[] $returnTypes */
	public function __construct(string $class, array $returnTypes)
	{
		$this->class = $class;
		$this->returnTypes = $returnTypes;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	/** @return Type[] */
	public function getReturnTypes(): array
	{
		return $this->returnTypes;
	}

	public function countReturnTypes(): int
	{
		return count($this->returnTypes);
	}

}
