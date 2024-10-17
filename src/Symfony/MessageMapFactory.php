<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use RuntimeException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use function count;
use function is_int;
use function is_null;
use function is_string;

// todo add tests
final class MessageMapFactory
{

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

			// todo handle abstract/parent services somehow?
			if (is_null($serviceClass)) {
				continue;
			}

			foreach ($service->getTags() as $tag) {
				// todo could there be more tags with the same name for the same service?
				// todo check if message handler tag name is constant or configurable
				if ($tag->getName() !== 'messenger.message_handler') {
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
					$variant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

					$returnTypesMap[$messageClassName][] = $variant->getReturnType();
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

		// todo handle if doesn't exists
		$methodReflection = $reflectionClass->getNativeMethod(self::DEFAULT_HANDLER_METHOD);

		$variant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
		$parameters = $variant->getParameters();

		if (count($parameters) !== 1) {
			// todo handle error
			throw new RuntimeException('invalid handler');
		}

		$type = $parameters[0]->getType();

		// todo many class names?
		foreach ($type->getObjectClassNames() as $className) {
			yield $className => ['method' => self::DEFAULT_HANDLER_METHOD];
		}
	}

}
