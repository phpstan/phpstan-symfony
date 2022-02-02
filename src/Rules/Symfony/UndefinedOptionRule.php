<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Symfony\Helper;
use PHPStan\Type\TypeUtils;
use function count;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class UndefinedOptionRule implements Rule
{

	/** @var ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	/** @var Standard */
	private $printer;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver, Standard $printer)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @return (string|RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return [];
		}

		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf(new ObjectType($classReflection->getName()))->yes()) {
			return [];
		}
		if (!(new ObjectType('Symfony\Component\Console\Input\InputInterface'))->isSuperTypeOf($scope->getType($node->var))->yes()) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || $node->name->name !== 'getOption') {
			return [];
		}
		if (!isset($node->getArgs()[0])) {
			return [];
		}

		$optType = $scope->getType($node->getArgs()[0]->value);
		$optStrings = TypeUtils::getConstantStrings($optType);
		if (count($optStrings) !== 1) {
			return [];
		}
		$optName = $optStrings[0]->getValue();

		$errors = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $name => $command) {
			try {
				$command->mergeApplicationDefinition();
				$command->getDefinition()->getOption($optName);
			} catch (InvalidArgumentException $e) {
				if ($scope->getType(Helper::createMarkerNode($node->var, $optType, $this->printer))->equals($optType)) {
					continue;
				}
				$errors[] = sprintf('Command "%s" does not define option "%s".', $name, $optName);
			}
		}

		return $errors;
	}

}
