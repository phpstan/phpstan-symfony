<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Symfony\Helper;
use PHPStan\Type\TypeUtils;
use function count;
use function sprintf;

final class UndefinedArgumentRule implements Rule
{

	/** @var \PHPStan\Symfony\ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	/** @var \PhpParser\PrettyPrinter\Standard */
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
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof MethodCall) {
			throw new ShouldNotHappenException();
		};

		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			throw new ShouldNotHappenException();
		}

		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf(new ObjectType($classReflection->getName()))->yes()) {
			return [];
		}
		if (!(new ObjectType('Symfony\Component\Console\Input\InputInterface'))->isSuperTypeOf($scope->getType($node->var))->yes()) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || $node->name->name !== 'getArgument') {
			return [];
		}
		if (!isset($node->args[0])) {
			return [];
		}

		$argType = $scope->getType($node->args[0]->value);
		$argStrings = TypeUtils::getConstantStrings($argType);
		if (count($argStrings) !== 1) {
			return [];
		}
		$argName = $argStrings[0]->getValue();

		$errors = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $name => $command) {
			try {
				$command->getDefinition()->getArgument($argName);
			} catch (InvalidArgumentException $e) {
				if ($scope->getType(Helper::createMarkerNode($node->var, $argType, $this->printer))->equals($argType)) {
					continue;
				}
				$errors[] = sprintf('Command "%s" does not define argument "%s".', $name, $argName);
			}
		}

		return $errors;
	}

}
