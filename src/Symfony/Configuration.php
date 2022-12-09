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

	public function getContainerXmlPaths(): ?array
	{
		if (isset($this->parameters['containerXmlPaths'])) {
			return $this->parameters['containerXmlPaths'];
		}

		if (isset($this->parameters['containerXmlPath'])) {
			return $this->parameters['containerXmlPath'];
		}

		if (isset($this->parameters['container_xml_path'])) {
			return $this->parameters['container_xml_path'];
		}

		return null;
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
