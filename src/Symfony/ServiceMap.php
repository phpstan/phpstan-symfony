<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeUtils;

final class ServiceMap
{

	/** @var Service[] */
	private $services = [];

	public function __construct(string $containerXml)
	{
		/** @var Service[] $aliases */
		$aliases = [];
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
			$service = new Service(
				(string) $attrs->id,
				isset($attrs->class) ? (string) $attrs->class : null,
				!isset($attrs->public) || (string) $attrs->public !== 'false',
				isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
				isset($attrs->alias) ? (string) $attrs->alias : null
			);
			if ($service->getAlias() !== null) {
				$aliases[] = $service;
			} else {
				$this->services[$service->getId()] = $service;
			}
		}
		foreach ($aliases as $service) {
			if ($service->getAlias() !== null && !array_key_exists($service->getAlias(), $this->services)) {
				continue;
			}
			$this->services[$service->getId()] = new Service(
				$service->getId(),
				$this->services[$service->getAlias()]->getClass(),
				$service->isPublic(),
				$service->isSynthetic(),
				null
			);
		}
	}

	public function getService(string $id): ?Service
	{
		return $this->services[$id] ?? null;
	}

	public static function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

}
