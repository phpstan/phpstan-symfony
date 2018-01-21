<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;

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
	 * @param Node $node
	 * @param Scope $scope
	 * @return mixed[]|null
	 */
	public function getServiceFromNode(Node $node, Scope $scope): ?array
	{
		$serviceId = self::getServiceIdFromNode($node, $scope);
		return $serviceId !== null && array_key_exists($serviceId, $this->services) ? $this->services[$serviceId] : null;
	}

	public static function getServiceIdFromNode(Node $node, Scope $scope): ?string
	{
		if ($node instanceof String_) {
			return $node->value;
		}
		if ($node instanceof ClassConstFetch && $node->class instanceof Name) {
			return $scope->resolveName($node->class);
		}
		if ($node instanceof Concat) {
			$left = self::getServiceIdFromNode($node->left, $scope);
			$right = self::getServiceIdFromNode($node->right, $scope);
			if ($left !== null && $right !== null) {
				return sprintf('%s%s', $left, $right);
			}
		}
		return null;
	}

}
