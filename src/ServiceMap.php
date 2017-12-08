<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

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
				'id' => (string) $attrs->id,
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
					'id' => $id,
					'class' => $this->services[$alias['alias']]['class'],
					'public' => $alias['public'],
					'synthetic' => $alias['synthetic'],
				];
			}
		}
	}

	/**
	 * @param Node $node
	 * @return array|null
	 */
	public function getServiceFromNode(Node $node)
	{
		$serviceId = self::getServiceIdFromNode($node);
		return $serviceId !== \null && \array_key_exists($serviceId, $this->services) ? $this->services[$serviceId] : \null;
	}

	/**
	 * @param Node $node
	 * @return string|null
	 */
	public static function getServiceIdFromNode(Node $node)
	{
		if ($node instanceof String_) {
			return $node->value;
		}
		if ($node instanceof ClassConstFetch && $node->class instanceof Name) {
			return $node->class->toString();
		}
		return \null;
	}

}
