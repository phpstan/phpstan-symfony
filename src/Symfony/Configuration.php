<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class Configuration
{

	/** @var array<string, mixed> */
	private array $parameters;

	/**
	 * @param array<string, mixed> $parameters
	 */
	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	public function getContainerXmlPath(): ?string
	{
		return $this->parameters['containerXmlPath'];
	}

	public function hasConstantHassers(): bool
	{
		return $this->parameters['constantHassers'];
	}

	public function getConsoleApplicationLoader(): ?string
	{
		return $this->parameters['consoleApplicationLoader'];
	}

}
