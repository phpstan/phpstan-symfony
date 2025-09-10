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
use function count;
use function is_null;

final class MessengerHandleTraitReturnTypeExtension implements ExpressionTypeResolverExtension
{

	private const TRAIT_NAME = 'Symfony\Component\Messenger\HandleTrait';
	private const TRAIT_METHOD_NAME = 'handle';

	private MessageMapFactory $messageMapFactory;

	private ?MessageMap $messageMap = null;

	public function __construct(MessageMapFactory $symfonyMessageMapFactory)
	{
		$this->messageMapFactory = $symfonyMessageMapFactory;
	}

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($this->isSupported($expr, $scope)) {
			$args = $expr->getArgs();
			if (count($args) !== 1) {
				return null;
			}

			$arg = $args[0]->value;
			$argClassNames = $scope->getType($arg)->getObjectClassNames();

			if (count($argClassNames) === 1) {
				$messageMap = $this->getMessageMap();
				$returnType = $messageMap->getTypeForClass($argClassNames[0]);

				if (!is_null($returnType)) {
					return $returnType;
				}
			}
		}

		return null;
	}

	private function getMessageMap(): MessageMap
	{
		if ($this->messageMap === null) {
			$this->messageMap = $this->messageMapFactory->create();
		}

		return $this->messageMap;
	}

	/**
	 * @phpstan-assert-if-true =MethodCall $expr
	 */
	private function isSupported(Expr $expr, Scope $scope): bool
	{
		if (!($expr instanceof MethodCall) || !($expr->name instanceof Identifier) || $expr->name->toLowerString() !== self::TRAIT_METHOD_NAME) {
			return false;
		}

		if (!$scope->isInClass()) {
			return false;
		}

		$methodReflection = $scope->getMethodReflection($scope->getType($expr->var), $expr->name->toString());
		if ($methodReflection === null) {
			return false;
		}

		$reflectionClass = $methodReflection->getDeclaringClass()->getNativeReflection();
		if (!$reflectionClass->hasMethod(self::TRAIT_METHOD_NAME)) {
			return false;
		}

		$traitMethodReflection = $reflectionClass->getMethod(self::TRAIT_METHOD_NAME);
		$declaringClassReflection = $traitMethodReflection->getBetterReflection()->getDeclaringClass();

		return $declaringClassReflection->getName() === self::TRAIT_NAME;
	}

}
