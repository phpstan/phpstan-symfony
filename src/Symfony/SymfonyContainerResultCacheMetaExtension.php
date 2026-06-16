<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;
use function array_map;
use function basename;
use function file_get_contents;
use function file_put_contents;
use function hash;
use function hash_file;
use function ksort;
use function sort;
use function sprintf;
use function var_export;

final class SymfonyContainerResultCacheMetaExtension implements ResultCacheMetaExtension
{

	private ParameterMap $parameterMap;

	private ServiceMap $serviceMap;

	private string $tmpDir;

	private ?string $containerXmlPath;

	public function __construct(ParameterMap $parameterMap, ServiceMap $serviceMap, string $tmpDir, ?string $containerXmlPath)
	{
		$this->parameterMap = $parameterMap;
		$this->serviceMap = $serviceMap;
		$this->tmpDir = $tmpDir;
		$this->containerXmlPath = $containerXmlPath;
	}

	public function getKey(): string
	{
		return 'symfonyDiContainer';
	}

	public function getHash(): string
	{
		if ($this->containerXmlPath !== null) {
			$xmlHash = hash_file('sha256', $this->containerXmlPath);
			if ($xmlHash === false) {
				throw new XmlContainerNotExistsException(sprintf('Container %s does not exist', $this->containerXmlPath));
			}
			$xmlHashFile = sprintf('%s/%s-%s.hash', $this->tmpDir, $this->getKey(), basename($this->containerXmlPath));
			$storedXmlHash = @file_get_contents($xmlHashFile);

			$hashForResultCacheFile = sprintf('%s/%s-%s-result-cache-meta.hash', $this->tmpDir, $this->getKey(), basename($this->containerXmlPath));
			$storedHashForResultCache = @file_get_contents($hashForResultCacheFile);
			if ($storedXmlHash === $xmlHash && $storedHashForResultCache !== false) {
				return $storedHashForResultCache;
			}
		}

		$hashForResultCache = $this->calculateHash();

		if ($this->containerXmlPath !== null) {
			file_put_contents($hashForResultCacheFile, $hashForResultCache);
			file_put_contents($xmlHashFile, $xmlHash);
		}

		return $hashForResultCache;
	}

	private function calculateHash(): string
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
