<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\VariadicPlaceholder;
use PhpParser\Node\Scalar\MagicConst\Method;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType;
use PHPStan\Type\TypeUtils;
use function count;

final class DefaultServiceMap implements ServiceMap
{
	const AUTOWIRE_ATTRIBUTE_CLASS = 'Symfony\Component\DependencyInjection\Attribute\Autowire';

	/** @var ServiceDefinition[] */
	private $services;

	/**
	 * @param ServiceDefinition[] $services
	 */
	public function __construct(array $services)
	{
		$this->services = $services;
	}

	/**
	 * @return ServiceDefinition[]
	 */
	public function getServices(): array
	{
		return $this->services;
	}

	public function getService(string $id): ?ServiceDefinition
	{
		return $this->services[$id] ?? null;
	}

	public static function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		if ($node instanceof Method) {
			$serviceId = self::getServiceSubscriberId($node, $scope);
			if (null !== $serviceId) {
				return $serviceId;
			}
		}

		$strings = TypeUtils::getConstantStrings($scope->getType($node));
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

	private static function getServiceSubscriberId(Expr $node, Scope $scope): ?string
	{
		$classReflection = $scope->getClassReflection();
		$functionName = $scope->getFunctionName();
		if ($classReflection === null ||
			$functionName === null ||
			!$classReflection->hasTraitUse('Symfony\Contracts\Service\ServiceSubscriberTrait')
		) {
			return null;
		}

		$methodReflection = $classReflection->getNativeReflection();
		$method = $methodReflection->getMethod($functionName);
		$attributes = $method->getAttributes('Symfony\Contracts\Service\Attribute\SubscribedService');
		if (count($attributes) !== 1) {
			return null;
		}

		$arguments = $attributes[0]->getArgumentsExpressions();

		$autowireArgs = isset($arguments['attributes']) ?
			self::getAutowireArguments($arguments['attributes'], $scope) :
			[];

		$value = null;
		$ignoreValue = false;
		foreach ($autowireArgs as $key => $arg) {
			if (!$arg instanceof Arg) {
				continue;
			}

			if ($arg->name !== null) {
				$argName = $arg->name->toString();
			} elseif ($key === 0) {
				$argName = 'value';
			} elseif ($key === 1) {
				$argName = 'service';
			} else {
				continue;
			}

			// `service` argument overrules others, so break loop
			$argValue = TypeUtils::getConstantStrings($scope->getType($arg->value));
			$argValue = count($argValue) === 1 ? $argValue[0]->getValue() : null;

			if (!is_string($argValue)) {
				continue;
			}

			if ($argName === 'service') {
				return $argValue;
			}

			if ($argName === 'value') {
				$value = str_starts_with($argValue, '@') &&
				!str_starts_with($argValue, '@@') &&
				!str_starts_with($argValue, '@=') ?
					substr($argValue, 1) :
					$argValue;
			} else {
				// Can't `break` because `service` has higher priority than others
				$ignoreValue = true;
			}
		}

		// If value is provided, and no other arguments with higher priority the value is the service id
		if (!$ignoreValue && $value !== null) {
			return $value;
		}

		$returnType = $method->getReturnType();
		return $returnType instanceof ReflectionNamedType ? $returnType->getName() : null;
	}

	/** @return array<Arg|VariadicPlaceholder> */
	private static function getAutowireArguments(Expr $expression, Scope $scope): array
	{
		if ($expression instanceof Expr\New_) {
			return $expression->class instanceof Name &&
					$scope->resolveName($expression->class) === self::AUTOWIRE_ATTRIBUTE_CLASS ?
				$expression->args :
				[];
		}

		if ($expression instanceof Expr\Array_) {
			foreach ($expression->items as $item) {
				if ($item === null) {
					continue;
				}

				$arg = $item->value;
				if ($arg instanceof Expr\New_) {
					return $arg->class instanceof Name &&
							$scope->resolveName($arg->class) === self::AUTOWIRE_ATTRIBUTE_CLASS ?
						$arg->args :
						[];
				}
			}
		}

		return [];
	}

}
