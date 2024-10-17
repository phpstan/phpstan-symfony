<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class ServiceTag implements ServiceTagDefinition
{

	/** @var string */
	private $name;

	/** @var array<string, string> */
	private $attributes;

	/** @param array<string, string> $attributes */
	public function __construct(string $name, array $attributes = [])
	{
		$this->name = $name;
		$this->attributes = $attributes;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAttributes(): array
	{
		return $this->attributes;
	}

}
