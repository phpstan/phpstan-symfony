<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function sprintf;

final class XmlParameterMapFactory implements ParameterMapFactory
{

	/** @var XmlContainerResolver */
	private $containerResolver;

	public function __construct(XmlContainerResolver $containerResolver)
	{
		$this->containerResolver = $containerResolver;
	}

	public function create(): ParameterMap
	{
		$container = $this->containerResolver->getContainer();

		if ($container === null) {
			return new FakeParameterMap();
		}

		/** @var \PHPStan\Symfony\Parameter[] $parameters */
		$parameters = [];
		foreach ($container->parameters->parameter as $def) {
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

}
