<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use PHPStan\Reflection\ReflectionProvider;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use function count;
use function is_int;
use function is_null;
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

			if (is_null($serviceClass)) {
				continue;
			}

			foreach ($service->getTags() as $tag) {
				if ($tag->getName() !== self::MESSENGER_HANDLER_TAG) {
					continue;
				}

				$tagAttributes = $tag->getAttributes();
				$reflectionClass = $this->reflectionProvider->getClass($serviceClass);

				if (isset($tagAttributes['handles'])) {
					$handles = [$tagAttributes['handles'] => ['method' => $tagAttributes['method'] ?? self::DEFAULT_HANDLER_METHOD]];
				} else {
					$handles = $this->guessHandledMessages($reflectionClass);
				}

				foreach ($handles as $messageClassName => $options) {
					$methodReflection = $reflectionClass->getNativeMethod($options['method'] ?? self::DEFAULT_HANDLER_METHOD);

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

	/** @return array<string, array<string, string>> */
	private function guessHandledMessages(ClassReflection $reflectionClass): iterable
	{
		if ($reflectionClass->implementsInterface(MessageSubscriberInterface::class)) {
			foreach ($reflectionClass->getName()::getHandledMessages() as $index => $value) {
				if (is_int($index) && is_string($value)) {
					yield $value => ['method' => self::DEFAULT_HANDLER_METHOD];
				} else {
					yield $index => $value;
				}
			}

			return;
		}

		try {
			$methodReflection = $reflectionClass->getNativeMethod(self::DEFAULT_HANDLER_METHOD);
		} catch (MissingMethodFromReflectionException $e) {
			return;
		}

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

}
