<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use function array_map;
use function hash;
use function ksort;
use function sort;
use function var_export;

final class SymfonyContainerResultCacheMetaExtension implements ResultCacheMetaExtension
{

	private ParameterMap $parameterMap;

	private ServiceMap $serviceMap;

	public function __construct(ParameterMap $parameterMap, ServiceMap $serviceMap)
	{
		$this->parameterMap = $parameterMap;
		$this->serviceMap = $serviceMap;
	}

	public function getKey(): string
	{
		return 'symfonyDiContainer';
	}

	public function getHash(): string
	{
		$services = $parameters = [];

		foreach ($this->parameterMap->getParameters() as $parameter) {
			$parameters[$parameter->getKey()] = $parameter->getValue();
		}
		ksort($parameters);

		foreach ($this->serviceMap->getServices() as $service) {
			$serviceTags = array_map(
				static fn (ServiceTag $tag) => [
					'name' => $tag->getName(),
					'attributes' => $tag->getAttributes(),
				],
				$service->getTags(),
			);
			sort($serviceTags);

			$services[$service->getId()] = [
				'class' => $service->getClass(),
				'public' => $service->isPublic() ? 'yes' : 'no',
				'synthetic' => $service->isSynthetic() ? 'yes' : 'no',
				'alias' => $service->getAlias(),
				'tags' => $serviceTags,
			];
		}
		ksort($services);

		return hash('sha256', var_export(['parameters' => $parameters, 'services' => $services], true));
	}

}
