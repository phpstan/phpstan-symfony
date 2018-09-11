<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class Service
{

	/** @var string */
	private $id;

	/** @var string|null */
	private $class;

	/** @var bool */
	private $public;

	/** @var bool */
	private $synthetic;

	/** @var string|null */
	private $alias;

	/** @var bool */
	private $hidden;

	/** @var string[] */
	private $methodCalls;

	/**
	 * @param string $id
	 * @param string|null $class
	 * @param bool $public
	 * @param bool $synthetic
	 * @param string|null $alias
	 * @param bool $hidden
	 * @param string[] $methodCalls
	 */
	public function __construct(
		string $id,
		?string $class,
		bool $public,
		bool $synthetic,
		?string $alias,
		bool $hidden,
		array $methodCalls
	)
	{
		$this->id = $id;
		$this->class = $class;
		$this->public = $public;
		$this->synthetic = $synthetic;
		$this->alias = $alias;
		$this->hidden = $hidden;
		$this->methodCalls = $methodCalls;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getClass(): ?string
	{
		return $this->class;
	}

	public function isPublic(): bool
	{
		return $this->public;
	}

	public function isSynthetic(): bool
	{
		return $this->synthetic;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function isHidden(): bool
	{
		return $this->hidden;
	}

	/** @return string[] */
	public function getMethodCalls(): array
	{
		return $this->methodCalls;
	}

}
