<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use RuntimeException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use function count;
use function is_int;
use function is_null;
use function is_string;

// todo add tests
final class MessageMapFactory
{

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
					$handles = isset($tag['method']) ? [$tag['handles'] => $tag['method']] : [$tag['handles']];
				} else {
					$handles = $this->guessHandledMessages($reflectionClass);
				}

				foreach ($handles as $messageClassName => $options) {
					if (is_int($messageClassName) && is_string($options)) {
						$messageClassName = $options;
						$options = [];
					}

					$options['method'] = $options['method'] ?? '__invoke';

					$methodReflection = $reflectionClass->getNativeMethod($options['method']);
					$variant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

					$returnTypesMap[$messageClassName][] = $variant->getReturnType();
				}
			}
		}

		$messages = [];
		foreach ($returnTypesMap as $messageClassName => $returnTypes) {
			$messages[] = new Message($messageClassName, $returnTypes);
		}

		return new MessageMap($messages);
	}

	private function guessHandledMessages(ClassReflection $reflectionClass): iterable
	{
		if ($reflectionClass->implementsInterface(MessageSubscriberInterface::class)) {
			// todo handle different return formats
			return $reflectionClass->getName()::getHandledMessages();
		}

		// todo handle if doesn't exists
		$methodReflection = $reflectionClass->getNativeMethod('__invoke');

		$variant = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
		$parameters = $variant->getParameters();

		if (count($parameters) !== 1) {
			// todo handle error
			throw new RuntimeException('invalid handler');
		}

		$type = $parameters[0]->getType();

		if ($type instanceof UnionType) {
			$types = [];
			foreach ($type->getTypes() as $type) {
				if (!$type instanceof ObjectType) {
					// todo handle error
					throw new RuntimeException('invalid handler');
				}

				$types[] = $type->getClassName();
			}

			if ($types) {
				return $types;
			}

			// todo handle error
			throw new RuntimeException('invalid handler');
		}

		if (!$type instanceof ObjectType) {
			throw new RuntimeException('invalid handler');
		}

		return [$type->getClassName()];
	}

}
