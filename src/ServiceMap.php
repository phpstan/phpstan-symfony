<?php
declare(strict_types=1);

namespace Lookyman\PHPStan\Symfony;

use Lookyman\PHPStan\Symfony\Exception\XmlContainerNotExistsException;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
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
		$xml = @\simplexml_load_file($containerXml);
		if ($xml === false) {
			throw new XmlContainerNotExistsException(\sprintf('Container %s not exists', $containerXml));
		}
		foreach ($xml->services->service as $def) {
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

	public function getServiceFromNode(Node $node): ?array
	{
		$serviceId = self::getServiceIdFromNode($node);
		return $serviceId !== \null && \array_key_exists($serviceId, $this->services) ? $this->services[$serviceId] : \null;
	}

	public static function getServiceIdFromNode(Node $node): ?string
	{
		if ($node instanceof String_) {
			return $node->value;
		}
		if ($node instanceof ClassConstFetch && $node->class instanceof Name) {
			$serviceId = $node->class->toString();
			if (!\in_array($serviceId, ['self', 'static', 'parent'], true)) {
				return $serviceId;
			}
		}
		if ($node instanceof Concat) {
			$left = self::getServiceIdFromNode($node->left);
			$right = self::getServiceIdFromNode($node->right);
			if ($left !== null && $right !== null) {
				return \sprintf('%s%s', $left, $right);
			}
		}
		return \null;
	}

}
