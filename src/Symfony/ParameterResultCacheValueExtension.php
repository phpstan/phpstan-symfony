<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheValueExtension;
use function hash;
use function var_export;

/**
 * A parameter in the container, for the files asking about it.
 */
final class ParameterResultCacheValueExtension implements ResultCacheValueExtension
{

	private ParameterMap $parameterMap;

	public function __construct(ParameterMap $symfonyParameterMap)
	{
		$this->parameterMap = $symfonyParameterMap;
	}

	public function getValue(string $key): string
	{
		$parameter = $this->parameterMap->getParameter($key);
		if ($parameter === null) {
			return 'missing';
		}

		return hash('sha256', var_export($parameter->getValue(), true));
	}

	public function keyToResultCache(string $key): string
	{
		return $key;
	}

	public function keyFromResultCache(string $storedKey): string
	{
		return $storedKey;
	}

}
