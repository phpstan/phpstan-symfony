<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use function array_map;
use function hash;
use function serialize;

final class SymfonyContainerResultCacheMetaExtension implements ResultCacheMetaExtension
{

	private ParameterMapFactory $parameterMapFactory;

	private ServiceMapFactory $serviceMapFactory;

	public function __construct(
		ParameterMapFactory $parameterMapFactory,
		ServiceMapFactory $serviceMapFactory
	)
	{
		$this->parameterMapFactory = $parameterMapFactory;
		$this->serviceMapFactory = $serviceMapFactory;
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
				$this->parameterMapFactory->create()->getParameters(),
			),
			'services' => array_map(
				static fn (ServiceDefinition $service) => [
					'id' => $service->getId(),
					'class' => $service->getClass(),
					'public' => $service->isPublic() ? 'yes' : 'no',
					'synthetic' => $service->isSynthetic() ? 'yes' : 'no',
					'alias' => $service->getAlias(),
				],
				$this->serviceMapFactory->create()->getServices(),
			),
		]));
	}

}
