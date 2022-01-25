<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

class UrlGeneratingRoute implements UrlGeneratingRoutesDefinition
{

	/** @var string */
	private $name;

	/** @var string */
	private $controller;

	public function __construct(
		string $name,
		string $controller
	)
	{
		$this->name = $name;
		$this->controller = $controller;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getController(): ?string
	{
		return $this->controller;
	}

}
