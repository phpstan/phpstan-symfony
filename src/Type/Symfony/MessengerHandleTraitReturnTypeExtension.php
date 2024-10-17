<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Symfony\MessageMap;
use PHPStan\Symfony\MessageMapFactory;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Type;
use ReflectionException;
use function count;
use function is_null;

final class MessengerHandleTraitReturnTypeExtension implements ExpressionTypeResolverExtension
{

	private const TRAIT_NAME = 'Symfony\Component\Messenger\HandleTrait';
	private const TRAIT_METHOD_NAME = 'handle';

	/** @var MessageMapFactory */
	private $messageMapFactory;

	/** @var MessageMap|null */
	private $messageMap;

	public function __construct(MessageMapFactory $symfonyMessageMapFactory)
	{
		$this->messageMapFactory = $symfonyMessageMapFactory;
	}

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		// todo handle different cases:
		//  - [X] regular message classes
		//  - [ ] interfaces for message classes
		//  - [ ] many handlers for one message? it would throw exception in HandleTrait anyway
		//  - [x] many messages for one handler
		//  - [partially] cover MessageSubscriberInterface
		//  - [partially] custom method names for handlers (different than default "__invoke" magic method)
		//  - [] read SF doc to determine any other cases to covers

		if ($this->isSupported($expr, $scope)) {
			$arg = $expr->getArgs()[0]->value;
			$argClassNames = $scope->getType($arg)->getObjectClassNames();

			// todo filter out not handled cases on map creation?
			if (count($argClassNames) === 1) {
				$messageMap = $this->getMessageMap();
				$message = $messageMap->getMessageForClass($argClassNames[0]);

				if (!is_null($message) && $message->countReturnTypes() === 1) {
					return $message->getReturnTypes()[0];
				}
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

	/**
	 * @phpstan-assert-if-true MethodCall $expr
	 */
	private function isSupported(Expr $expr, Scope $scope): bool
	{
		if (!($expr instanceof MethodCall) || !($expr->name instanceof Identifier) || $expr->name->name !== self::TRAIT_METHOD_NAME) {
			return false;
		}

		if (!$scope->isInClass()) {
			return false;
		}

		try {
			$methodReflection = $scope->getClassReflection()->getNativeReflection()->getMethod(self::TRAIT_METHOD_NAME);
			$declaringClassReflection = $methodReflection->getBetterReflection()->getDeclaringClass();

			return $declaringClassReflection->getName() === self::TRAIT_NAME;
		} catch (ReflectionException $e) {
			return false;
		}
	}

}
