<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony;

final class ServiceMap
{

	/**
	 * @var array
	 */
	private $services;

	public function __construct(string $containerXml)
	{
		$this->services = $aliases = [];
		/** @var \SimpleXMLElement $def */
		foreach (\simplexml_load_file($containerXml)->services->service as $def) {
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}
			$service = [
				'class' => isset($attrs->class) ? (string) $attrs->class : \null,
				'public' => !isset($attrs->public) || (string) $attrs->public !== 'false',
				'synthetic' => isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
			];
			if (isset($attrs->alias)) {
				$aliases[(string) $attrs->id] = \array_merge($service, ['alias' => (string) $attrs->alias]);
			} else {
				$this->services[(string) $attrs->id] = $service;
			}
		}
		foreach ($aliases as $id => $alias) {
			if (\array_key_exists($alias['alias'], $this->services)) {
				$this->services[$id] = [
					'class' => $this->services[$alias['alias']]['class'],
					'public' => $alias['public'],
					'synthetic' => $alias['synthetic'],
				];
			}
		}
	}

	public function getServices(): array
	{
		return $this->services;
	}

}
