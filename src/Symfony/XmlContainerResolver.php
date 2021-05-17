<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\ShouldNotHappenException;
use SimpleXMLElement;

final class XmlContainerResolver
{

	/** @var string|null */
	private $containerXmlPath;

	/** @var SimpleXMLElement|null */
	private $container;

	public function __construct(?string $containerXmlPath)
	{
		$this->containerXmlPath = $containerXmlPath;
	}

	public function getContainer(): ?SimpleXMLElement
	{
		if ($this->containerXmlPath === null) {
			return null;
		}

		if ($this->container !== null) {
			return $this->container;
		}

		if (pathinfo($this->containerXmlPath, PATHINFO_EXTENSION) === 'php') {
			$fileContents = require $this->containerXmlPath;

			if (!is_string($fileContents)) {
				throw new ShouldNotHappenException();
			}
		} else {
			$fileContents = file_get_contents($this->containerXmlPath);
			if ($fileContents === false) {
				throw new XmlContainerNotExistsException(sprintf('Container %s does not exist', $this->containerXmlPath));
			}
		}

		$container = @simplexml_load_string($fileContents);
		if ($container === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s cannot be parsed', $this->containerXmlPath));
		}

		$this->container = $container;

		return $this->container;
	}

}
