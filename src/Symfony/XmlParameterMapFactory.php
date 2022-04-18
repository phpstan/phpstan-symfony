<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use InvalidArgumentException;
use PHPStan\ShouldNotHappenException;
use SimpleXMLElement;
use function base64_decode;
use function file_get_contents;
use function is_numeric;
use function simplexml_load_string;
use function sprintf;
use function strpos;

final class XmlParameterMapFactory implements ParameterMapFactory
{

	/** @var string|null */
	private $containerXml;

	public function __construct(Configuration $configuration)
	{
		$this->containerXml = $configuration->getContainerXmlPath();
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

		/** @var Parameter[] $parameters */
		$parameters = [];
		foreach ($xml->parameters->parameter as $def) {
			/** @var SimpleXMLElement $attrs */
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
	private function getNodeValue(SimpleXMLElement $def)
	{
		/** @var SimpleXMLElement $attrs */
		$attrs = $def->attributes();

		$value = null;
		switch ((string) $attrs->type) {
			case 'collection':
				$value = [];
				$children = $def->children();
				if ($children === null) {
					throw new ShouldNotHappenException();
				}
				foreach ($children as $child) {
					/** @var SimpleXMLElement $childAttrs */
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
					throw new InvalidArgumentException(sprintf('Parameter "%s" of binary type is not valid base64 encoded string.', (string) $attrs->key));
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

}
