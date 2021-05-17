<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function strpos;
use function substr;

final class XmlServiceMapFactory implements ServiceMapFactory
{

	/** @var XmlContainerResolver */
	private $containerResolver;

	public function __construct(XmlContainerResolver $containerResolver)
	{
		$this->containerResolver = $containerResolver;
	}

	public function create(): ServiceMap
	{
		$container = $this->containerResolver->getContainer();

		if ($container === null) {
			return new FakeServiceMap();
		}

		/** @var \PHPStan\Symfony\Service[] $services */
		$services = [];
		/** @var \PHPStan\Symfony\Service[] $aliases */
		$aliases = [];
		foreach ($container->services->service as $def) {
			/** @var \SimpleXMLElement $attrs */
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}

			$service = new Service(
				strpos((string) $attrs->id, '.') === 0 ? substr((string) $attrs->id, 1) : (string) $attrs->id,
				isset($attrs->class) ? (string) $attrs->class : null,
				!isset($attrs->public) || (string) $attrs->public !== 'false',
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

}
