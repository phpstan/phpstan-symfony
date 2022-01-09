<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

final class Configuration
{

	/** @var array<string, mixed> */
	private $parameters;

	/**
	 * @param array<string, mixed> $parameters
	 */
	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	public function getContainerXmlPath(): ?string
	{
		return $this->parameters['containerXmlPath'] ?? $this->parameters['container_xml_path'] ?? null;
	}

	public function hasConstantHassers(): bool
	{
		return $this->parameters['constantHassers'] ?? $this->parameters['constant_hassers'] ?? true;
	}

	public function getConsoleApplicationLoader(): ?string
	{
		return $this->parameters['consoleApplicationLoader'] ?? $this->parameters['console_application_loader'] ?? null;
	}

}
