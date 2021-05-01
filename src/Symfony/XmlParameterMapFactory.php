<?php declare(strict_types=1);

namespace PHPStan\Symfony;

use function sprintf;

final class XmlParameterMapFactory implements ParameterMapFactory
{

	/** @var string|null */
	private $containerXml;

	public function __construct(?string $containerXml)
	{
		$this->containerXml = $containerXml;
	}

	public function create(): ParameterMap
	{
		if ($this->containerXml === null) {
			return new FakeParameterMap();
		}

		$fileContents = file_get_contents($this->containerXml);
		if ($fileContents === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s does not exist', $this->containerXml));
		}

		$xml = @simplexml_load_string($fileContents);
		if ($xml === false) {
			throw new XmlContainerNotExistsException(sprintf('Container %s cannot be parsed', $this->containerXml));
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
					/** @var \SimpleXMLElement $attrs */
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
				if (false === $value = base64_decode((string) $def, true)) {
					throw new \InvalidArgumentException(sprintf('Tag "<%s>" with type="binary" is not a valid base64 encoded string.', (string) $attrs->key));
				}

				break;

			default:
				$value = (string) $def;

				if (is_numeric($value)) {
					if (false !== strpos($value, '.')) {
						$value = (float) $value;
					} else {
						$value = (int) $value;
					}
				} else if ($value === 'true') {
					$value = true;
				} else if ($value === 'false') {
					$value = false;
				}
		}

		return $value;
	}
}
