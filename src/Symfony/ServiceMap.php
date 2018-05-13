<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;

final class ServiceMap
{

	/** @var array<string, array> */
	private $services;

	public function __construct(string $containerXml)
	{
		$this->services = $aliases = [];
		/** @var \SimpleXMLElement|false $xml */
		$xml = @simplexml_load_file($containerXml);
		if ($xml === false) {
			throw new \PHPStan\Symfony\XmlContainerNotExistsException(sprintf('Container %s not exists', $containerXml));
		}
		foreach ($xml->services->service as $def) {
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}
			$service = [
				'id' => (string) $attrs->id,
				'class' => isset($attrs->class) ? (string) $attrs->class : null,
				'public' => !isset($attrs->public) || (string) $attrs->public !== 'false',
				'synthetic' => isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
			];
			if (isset($attrs->alias)) {
				$aliases[(string) $attrs->id] = array_merge($service, ['alias' => (string) $attrs->alias]);
			} else {
				$this->services[(string) $attrs->id] = $service;
			}
		}
		foreach ($aliases as $id => $alias) {
			if (!array_key_exists($alias['alias'], $this->services)) {
				continue;
			}
			$this->services[$id] = [
				'id' => $id,
				'class' => $this->services[$alias['alias']]['class'],
				'public' => $alias['public'],
				'synthetic' => $alias['synthetic'],
			];
		}
	}

	/**
	 * @param string $id
	 * @return mixed[]|null
	 */
	public function getService(string $id): ?array
	{
		return $this->services[$id] ?? null;
	}

	public static function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		$nodeType = $scope->getType($node);
		return $nodeType instanceof ConstantStringType ? $nodeType->getValue() : null;
	}

}
