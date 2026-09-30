<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheValueExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use function class_exists;
use function hash;
use function strpos;
use function var_export;

/**
 * A service in the container, for the files asking about it: its class, whether it's public
 * and whether it's synthetic. When the class is a parameter, the parameters are part of it too.
 */
final class ServiceResultCacheValueExtension implements ResultCacheValueExtension
{

	private ServiceMap $serviceMap;

	private ParameterMap $parameterMap;

	public function __construct(ServiceMap $symfonyServiceMap, ParameterMap $symfonyParameterMap)
	{
		$this->serviceMap = $symfonyServiceMap;
		$this->parameterMap = $symfonyParameterMap;
	}

	public function getValue(string $key): string
	{
		$service = $this->serviceMap->getService($key);
		if ($service === null) {
			return 'missing';
		}

		$class = $service->getClass();

		return hash('sha256', var_export([
			'class' => $class,
			'resolvedClass' => $class !== null && strpos($class, '%') !== false ? $this->resolveClass($class) : null,
			'public' => $service->isPublic(),
			'synthetic' => $service->isSynthetic(),
		], true));
	}

	public function keyToResultCache(string $key): string
	{
		return $key;
	}

	public function keyFromResultCache(string $storedKey): string
	{
		return $storedKey;
	}

	/**
	 * @return mixed
	 */
	private function resolveClass(string $class)
	{
		if (!class_exists(ParameterBag::class)) {
			return null;
		}

		$parameters = [];
		foreach ($this->parameterMap->getParameters() as $parameter) {
			$parameters[$parameter->getKey()] = $parameter->getValue();
		}

		return (new ParameterBag($parameters))->resolveValue($class);
	}

}
