<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use function class_exists;
use function count;
use function is_array;
use function is_int;
use function is_string;

final class MessageMapFactory
{

	private const MESSENGER_HANDLER_TAG = 'messenger.message_handler';
	private const DEFAULT_HANDLER_METHOD = '__invoke';

	/** @var ReflectionProvider */
	private $reflectionProvider;

	/** @var ServiceMap */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap, ReflectionProvider $reflectionProvider)
	{
		$this->serviceMap = $symfonyServiceMap;
		$this->reflectionProvider = $reflectionProvider;
	}

	public function create(): MessageMap
	{
		$returnTypesMap = [];

		foreach ($this->serviceMap->getServices() as $service) {
			$serviceClass = $service->getClass();

			if ($serviceClass === null) {
				continue;
			}

			foreach ($service->getTags() as $tag) {
				if ($tag->getName() !== self::MESSENGER_HANDLER_TAG) {
					continue;
				}

				if (!$this->reflectionProvider->hasClass($serviceClass)) {
					continue;
				}

				$reflectionClass = $this->reflectionProvider->getClass($serviceClass);

				/** @var array{handles?: class-string, method?: string} $tagAttributes */
				$tagAttributes = $tag->getAttributes();

				if (isset($tagAttributes['handles'])) {
					$handles = [$tagAttributes['handles'] => ['method' => $tagAttributes['method'] ?? self::DEFAULT_HANDLER_METHOD]];
				} else {
					$handles = $this->guessHandledMessages($reflectionClass);
				}

				foreach ($handles as $messageClassName => $options) {
					$methodName = $options['method'] ?? self::DEFAULT_HANDLER_METHOD;

					if (!$reflectionClass->hasNativeMethod($methodName)) {
						continue;
					}

					$methodReflection = $reflectionClass->getNativeMethod($methodName);

					foreach ($methodReflection->getVariants() as $variant) {
						$returnTypesMap[$messageClassName][] = $variant->getReturnType();
					}
				}
			}
		}

		$messageMap = [];
		foreach ($returnTypesMap as $messageClassName => $returnTypes) {
			if (count($returnTypes) !== 1) {
				continue;
			}

			$messageMap[$messageClassName] = $returnTypes[0];
		}

		return new MessageMap($messageMap);
	}

	/** @return iterable<string, array<string, string>> */
	private function guessHandledMessages(ClassReflection $reflectionClass): iterable
	{
		if ($reflectionClass->implementsInterface(MessageSubscriberInterface::class)) {
			$className = $reflectionClass->getName();

			foreach ($className::getHandledMessages() as $index => $value) {
				$containOptions = self::containOptions($index, $value);
				if ($containOptions === true) {
					yield $index => $value;
				} elseif ($containOptions === false) {
					yield $value => ['method' => self::DEFAULT_HANDLER_METHOD];
				}
			}

			return;
		}

		if (!$reflectionClass->hasNativeMethod(self::DEFAULT_HANDLER_METHOD)) {
			return;
		}

		$methodReflection = $reflectionClass->getNativeMethod(self::DEFAULT_HANDLER_METHOD);

		$variants = $methodReflection->getVariants();
		if (count($variants) !== 1) {
			return;
		}

		$parameters = $variants[0]->getParameters();

		if (count($parameters) !== 1) {
			return;
		}

		$classNames = $parameters[0]->getType()->getObjectClassNames();

		if (count($classNames) !== 1) {
			return;
		}

		yield $classNames[0] => ['method' => self::DEFAULT_HANDLER_METHOD];
	}

	/**
	 * @param mixed $index
	 * @param mixed $value
	 * @phpstan-assert-if-true =class-string $index
	 * @phpstan-assert-if-true =array<string, mixed> $value
	 * @phpstan-assert-if-false =int $index
	 * @phpstan-assert-if-false =class-string $value
	 */
	private static function containOptions($index, $value): ?bool
	{
		if (is_string($index) && class_exists($index) && is_array($value)) {
			return true;
		} elseif (is_int($index) && is_string($value) && class_exists($value)) {
			return false;
		}

		return null;
	}

}
