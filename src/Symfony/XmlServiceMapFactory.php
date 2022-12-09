<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use SimpleXMLElement;
use function file_get_contents;
use function simplexml_load_string;
use function sprintf;
use function strpos;
use function substr;

final class XmlServiceMapFactory implements ServiceMapFactory
{

	/** @var string|null */
	private $containersXml;

	public function __construct(Configuration $configuration)
	{
		$this->containersXml = $configuration->getContainerXmlPaths();
	}

	public function create(): ServiceMap
	{
		if ($this->containersXml === null) {
			return new FakeServiceMap();
		}

		/** @var Service[] $services */
		$services = [];
		/** @var Service[] $aliases */
		$aliases = [];

		foreach ($this->containersXml  as $containerXml) {
			$fileContents = file_get_contents($containerXml);
			if ($fileContents === false) {
				throw new XmlContainerNotExistsException(sprintf('Container %s does not exist', $containerXml));
			}

			$xml = @simplexml_load_string($fileContents);
			if ($xml === false) {
				throw new XmlContainerNotExistsException(sprintf('Container %s cannot be parsed', $containerXml));
			}

			foreach ($xml->services->service as $def) {
				/** @var SimpleXMLElement $attrs */
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
					if (
						isset($services[$service->getId()])
						&& (serialize($service) !== serialize($services[$service->getId()]))
					) {
//						var_dump($containerXml);
//						var_dump($service->getId());
//						var_dump($service == $services[$service->getId()] );
//						var_dump($service, $services[$service->getId()] );
//						var_dump(serialize($service));
//						var_dump(serialize($services[$service->getId()]));
//						var_dump(serialize($service) === serialize($services[$service->getId()]));
//						die;
					}
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
		}

		return new DefaultServiceMap($services);
	}

}
