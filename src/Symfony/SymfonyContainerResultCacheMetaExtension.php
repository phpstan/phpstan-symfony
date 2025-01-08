<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use function array_map;
use function hash;
use function serialize;
use function sort;

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
				static fn (ParameterDefinition $parameter): array => [
					'name' => $parameter->getKey(),
					'value' => $parameter->getValue(),
				],
				$this->parameterMap->getParameters(),
			),
			'services' => array_map(
				static function (ServiceDefinition $service): array {
					$serviceTags = array_map(
						static fn (ServiceTag $tag) => [
							'name' => $tag->getName(),
							'attributes' => $tag->getAttributes(),
						],
						$service->getTags(),
					);
					sort($serviceTags);

					return [
						'id' => $service->getId(),
						'class' => $service->getClass(),
						'public' => $service->isPublic() ? 'yes' : 'no',
						'synthetic' => $service->isSynthetic() ? 'yes' : 'no',
						'alias' => $service->getAlias(),
						'tags' => $serviceTags,
					];
				},
				$this->serviceMap->getServices(),
			),
		]));
	}

}
