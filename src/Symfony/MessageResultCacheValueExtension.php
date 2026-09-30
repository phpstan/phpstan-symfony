<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Analyser\ResultCache\ResultCacheValueExtension;
use PHPStan\Type\VerbosityLevel;

/**
 * What handling a message returns, for the files handling it through HandleTrait. It comes from
 * the handlers in the container, and from their code.
 */
final class MessageResultCacheValueExtension implements ResultCacheValueExtension
{

	private MessageMapFactory $messageMapFactory;

	private ?MessageMap $messageMap = null;

	public function __construct(MessageMapFactory $symfonyMessageMapFactory)
	{
		$this->messageMapFactory = $symfonyMessageMapFactory;
	}

	public function getValue(string $key): string
	{
		if ($this->messageMap === null) {
			$this->messageMap = $this->messageMapFactory->create();
		}

		$type = $this->messageMap->getTypeForClass($key);
		if ($type === null) {
			return 'none';
		}

		return $type->describe(VerbosityLevel::precise());
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
