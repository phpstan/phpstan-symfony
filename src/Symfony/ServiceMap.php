<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeUtils;

final class ServiceMap
{

	/** @var Service[] */
	private $services = [];

	/** @var string[][] */
	private $serviceClasses = [];

	public function __construct(string $containerXml)
	{
		/** @var Service[] $aliases */
		$aliases = [];
		/** @var \SimpleXMLElement|false $xml */
		$xml = @simplexml_load_file($containerXml);
		if ($xml === false) {
			throw new \PHPStan\Symfony\XmlContainerNotExistsException(
				sprintf('Container %s not exists', $containerXml)
			);
		}
		foreach ($xml->services->service as $def) {
			$attrs = $def->attributes();
			if (!isset($attrs->id)) {
				continue;
			}
			$serviceId = strpos((string) $attrs->id, '.') === 0 ? substr((string) $attrs->id, 1) : (string) $attrs->id;
			$service = new Service(
				$serviceId,
				isset($attrs->class) ? (string) $attrs->class : null,
				!isset($attrs->public) || (string) $attrs->public !== 'false',
				isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
				isset($attrs->alias) ? (string) $attrs->alias : null,
				strpos((string) $attrs->id, '.') === 0,
				$this->getMethodCalls($def)
			);
			if ($service->getAlias() !== null) {
				$aliases[] = $service;
			} else {
				$this->services[$service->getId()] = $service;
			}
			if (!isset($attrs->class)) {
				continue;
			}
			$this->serviceClasses[(string) $attrs->class][] = $serviceId;
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
				$service->getAlias(),
				$service->isHidden(),
				$service->getMethodCalls()
			);
		}
	}

	/**
	 * @param \SimpleXMLElement $def
	 * @return string[]
	 */
	private function getMethodCalls(\SimpleXMLElement $def): array
	{
		$methodCalls = [];
		foreach ($def as $type => $element) {
			if ($type !== 'call') {
				continue;
			}
			$attrs = $element->attributes();
			$methodCalls[] = (string) $attrs->method;
		}

		return $methodCalls;
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

	/**
	 * @param string $classname
	 * @return string[]|null
	 */
	public function getServiceIdsFromClassname(string $classname): ?array
	{
		return @$this->serviceClasses[$classname];
	}

}
