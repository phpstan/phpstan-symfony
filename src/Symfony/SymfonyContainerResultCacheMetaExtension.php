<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use function array_map;
use function hash;
use function serialize;

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
		return hash('sha256', serialize([
			'parameters' => array_map(
				static fn (ParameterDefinition $parameter) => [
					'name' => $parameter->getKey(),
					'value' => $parameter->getValue(),
				],
				$this->parameterMap->getParameters(),
			),
			'services' => array_map(
				static fn (ServiceDefinition $service) => [
					'id' => $service->getId(),
					'class' => $service->getClass(),
					'public' => $service->isPublic() ? 'yes' : 'no',
					'synthetic' => $service->isSynthetic() ? 'yes' : 'no',
					'alias' => $service->getAlias(),
				],
				$this->serviceMap->getServices(),
			),
		]));
	}

}
