<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Symfony\MessageMap;
use PHPStan\Symfony\MessageMapFactory;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Type;
use function count;
use function in_array;
use function is_null;

/**
 * Configurable extension for resolving return types of methods that internally use HandleTrait.
 *
 * Configured via PHPStan parameters under symfony.messenger.handleTraitWrappers with
 * "class::method" patterns, e.g.:
 * - App\Bus\QueryBus::dispatch
 * - App\Bus\QueryBus::query
 * - App\Bus\CommandBus::execute
 * - App\Bus\CommandBus::handle
 */
final class MessengerHandleTraitWrapperReturnTypeExtension implements ExpressionTypeResolverExtension
{

	private MessageMapFactory $messageMapFactory;

	private ?MessageMap $messageMap = null;

	/** @var array<string> */
	private array $wrappers;

	private ReflectionProvider $reflectionProvider;

	/** @param array{handleTraitWrappers: array<string>}|null $messenger */
	public function __construct(MessageMapFactory $messageMapFactory, ?array $messenger, ReflectionProvider $reflectionProvider)
	{
		$this->messageMapFactory = $messageMapFactory;
		$this->wrappers = $messenger['handleTraitWrappers'] ?? [];
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if (!$this->isSupported($expr, $scope)) {
			return null;
		}

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

		return null;
	}

	/**
	 * @phpstan-assert-if-true =MethodCall $expr
	 */
	private function isSupported(Expr $expr, Scope $scope): bool
	{
		if ($this->wrappers === []) {
			return false;
		}

		if (!($expr instanceof MethodCall) || !($expr->name instanceof Identifier)) {
			return false;
		}

		$methodName = $expr->name->name;
		$varType = $scope->getType($expr->var);
		$classNames = $varType->getObjectClassNames();

		if (count($classNames) !== 1) {
			return false;
		}

		$className = $classNames[0];
		$classMethodCombination = $className . '::' . $methodName;

		// Check if this exact class::method combination is configured
		if (in_array($classMethodCombination, $this->wrappers, true)) {
			return true;
		}

		// Check if any interface implemented by this class::method is configured
		if ($this->reflectionProvider->hasClass($className)) {
			$classReflection = $this->reflectionProvider->getClass($className);
			foreach ($classReflection->getInterfaces() as $interface) {
				$interfaceMethodCombination = $interface->getName() . '::' . $methodName;
				if (in_array($interfaceMethodCombination, $this->wrappers, true)) {
					return true;
				}
			}
		}

		return false;
	}

	private function getMessageMap(): MessageMap
	{
		if ($this->messageMap === null) {
			$this->messageMap = $this->messageMapFactory->create();
		}

		return $this->messageMap;
	}

}
