<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class Parameter implements ParameterDefinition
{

	/** @var string */
	private $key;

	/** @var array<mixed>|bool|float|int|string */
	private $value;

	/**
	 * @param array<mixed>|bool|float|int|string $value
	 */
	public function __construct(
		string $key,
		$value
	)
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @return array<mixed>|bool|float|int|string
	 */
	public function getValue()
	{
		return $this->value;
	}

}
