<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function sprintf;

final class XmlParameterMapFactory implements ParameterMapFactory
{

	/** @var string|null */
	private $containerXml;

	/** @var array<int, string> */
	private $paths;

	public function __construct(Configuration $configuration)
	{
		$this->containerXml = $configuration->getContainerXmlPath();
		$this->paths = $configuration->getContainerXmlPaths();
	}

	public function create(): ParameterMap
	{
		$this->addSingleContainerToPaths();

		if (count($this->paths) === 0) {
			return new FakeParameterMap();
		}

		foreach ($this->paths as $path) {
			try {
				return $this->loadContainer($path);
			} catch (XmlContainerNotExistsException $e) {
				continue;
			}
		}

		throw new XmlContainerNotExistsException(
			'Container not found. Attempted to load:' . PHP_EOL .
			implode(PHP_EOL, $this->paths)
		);
	}

	private function loadContainer(string $path): DefaultParameterMap
	{
		if (file_exists($path) === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s does not exist', $path));
		}

		$fileContents = file_get_contents($path);
		if ($fileContents === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s could not load the content', $path));
		}

		$xml = @simplexml_load_string($fileContents);
		if ($xml === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s cannot be parsed', $path));
		}

		/** @var \PHPStan\Symfony\Parameter[] $parameters */
		$parameters = [];
		foreach ($xml->parameters->parameter as $def) {
			/** @var \SimpleXMLElement $attrs */
			$attrs = $def->attributes();

			$parameter = new Parameter(
				(string) $attrs->key,
				$this->getNodeValue($def)
			);

			$parameters[$parameter->getKey()] = $parameter;
		}

		return new DefaultParameterMap($parameters);
	}

	/**
	 * @return array<mixed>|bool|float|int|string
	 */
	private function getNodeValue(\SimpleXMLElement $def)
	{
		/** @var \SimpleXMLElement $attrs */
		$attrs = $def->attributes();

		$value = null;
		switch ((string) $attrs->type) {
			case 'collection':
				$value = [];
				foreach ($def->children() as $child) {
					/** @var \SimpleXMLElement $childAttrs */
					$childAttrs = $child->attributes();

					if (isset($childAttrs->key)) {
						$value[(string) $childAttrs->key] = $this->getNodeValue($child);
					} else {
						$value[] = $this->getNodeValue($child);
					}
				}
				break;

			case 'string':
				$value = (string) $def;
				break;

			case 'binary':
				$value = base64_decode((string) $def, true);
				if ($value === false) {
					throw new \InvalidArgumentException(sprintf('Parameter "%s" of binary type is not valid base64 encoded string.', (string) $attrs->key));
				}

				break;

			default:
				$value = (string) $def;

				if (is_numeric($value)) {
					if (strpos($value, '.') !== false) {
						$value = (float) $value;
					} else {
						$value = (int) $value;
					}
				} elseif ($value === 'true') {
					$value = true;
				} elseif ($value === 'false') {
					$value = false;
				}
		}

		return $value;
	}

	private function addSingleContainerToPaths(): void
	{
		$containerXml = $this->containerXml;

		if ($containerXml === null) {
			return;
		}

		$this->paths = array_merge(
			[$containerXml],
			$this->paths
		);
	}

}
