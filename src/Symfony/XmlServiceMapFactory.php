<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function simplexml_load_string;
use function sprintf;
use function strpos;
use function substr;

final class XmlServiceMapFactory implements ServiceMapFactory
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

	public function create(): ServiceMap
	{
		$this->addSingleContainerToPaths();

		if (count($this->paths) === 0) {
			return new FakeServiceMap();
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

	private function loadContainer(string $path): DefaultServiceMap
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

		/** @var \PHPStan\Symfony\Service[] $services */
		$services = [];
		/** @var \PHPStan\Symfony\Service[] $aliases */
		$aliases = [];
		foreach ($xml->services->service as $def) {
			/** @var \SimpleXMLElement $attrs */
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}

			$service = new Service(
				strpos((string) $attrs->id, '.') === 0 ? substr((string) $attrs->id, 1) : (string) $attrs->id,
				isset($attrs->class) ? (string) $attrs->class : null,
				isset($attrs->public) && (string) $attrs->public === 'true',
				isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
				isset($attrs->alias) ? (string) $attrs->alias : null
			);

			if ($service->getAlias() !== null) {
				$aliases[] = $service;
			} else {
				$services[$service->getId()] = $service;
			}
		}
		foreach ($aliases as $service) {
			$alias = $service->getAlias();
			if ($alias !== null && !isset($services[$alias])) {
				continue;
			}
			$id = $service->getId();
			$services[$id] = new Service(
				$id,
				$services[$alias]->getClass(),
				$service->isPublic(),
				$service->isSynthetic(),
				$alias
			);
		}

		return new DefaultServiceMap($services);
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
