<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use MessengerHandleTrait\HandleTraitClass;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\MessageMap;
use PHPStan\Symfony\MessageMapFactory;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use function count;
use function is_null;

final class MessengerHandleTraitDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var MessageMapFactory */
	private $messageMapFactory;

	/** @var MessageMap|null */
	private $messageMap;

	public function __construct(MessageMapFactory $symfonyMessageMapFactory)
	{
		$this->messageMapFactory = $symfonyMessageMapFactory;
	}

	public function getClass(): string
	{
		// todo traits are not supported yet in phpstan for this extension (https://github.com/phpstan/phpstan/issues/5761)
		// return HandleTrait::class;

		// todo or make it configurable with passing concrete classes names to extension config
		// todo or use reflection somehow to get all classes that use HandleTrait and configure it dynamically

		// todo temporarily hardcoded test class here
		return HandleTraitClass::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		// todo additional reflection checker that it comes only from trait?
		return $methodReflection->getName() === 'handle';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		// todo handle different cases:
		//  - [X] regular message classes
		//  - [ ] interfaces for message classes
		//  - [ ] many handlers for one message? it would throw exception in HandleTrait anyway
		//  - [x] many messages for one handler
		//  - [partially] cover MessageSubscriberInterface
		//  - [partially] custom method names for handlers (different than default "__invoke" magic method)
		//  - [] read SF doc to determine any other cases to covers

		$arg = $methodCall->getArgs()[0]->value;
		$argClassNames = $scope->getType($arg)->getObjectClassNames();

		// todo filter out not handled cases on map creation?
		if (count($argClassNames) === 1) {
			$messageMap = $this->getMessageMap();
			$message = $messageMap->getMessageForClass($argClassNames[0]);

			if (!is_null($message) && $message->countReturnTypes() === 1) {
				return $message->getReturnTypes()[0];
			}
		}

		return null;
	}

	private function getMessageMap(): MessageMap
	{
		if (is_null($this->messageMap)) {
			$this->messageMap = $this->messageMapFactory->create();
		}

		return $this->messageMap;
	}

}
