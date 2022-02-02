<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use function count;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class InvalidArgumentDefaultValueRule implements Rule
{

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @return (string|RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf($scope->getType($node->var))->yes()) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || $node->name->name !== 'addArgument') {
			return [];
		}
		if (!isset($node->getArgs()[3])) {
			return [];
		}

		$modeType = isset($node->getArgs()[1]) ? $scope->getType($node->getArgs()[1]->value) : new NullType();
		if ($modeType instanceof NullType) {
			$modeType = new ConstantIntegerType(2); // InputArgument::OPTIONAL
		}
		$modeTypes = TypeUtils::getConstantScalars($modeType);
		if (count($modeTypes) !== 1) {
			return [];
		}
		if (!$modeTypes[0] instanceof ConstantIntegerType) {
			return [];
		}
		$mode = $modeTypes[0]->getValue();

		$defaultType = $scope->getType($node->getArgs()[3]->value);

		// not an array
		if (($mode & 4) !== 4 && !(new UnionType([new StringType(), new NullType()]))->isSuperTypeOf($defaultType)->yes()) {
			return [sprintf('Parameter #4 $default of method Symfony\Component\Console\Command\Command::addArgument() expects string|null, %s given.', $defaultType->describe(VerbosityLevel::typeOnly()))];
		}

		// is array
		if (($mode & 4) === 4 && !(new UnionType([new ArrayType(new IntegerType(), new StringType()), new NullType()]))->isSuperTypeOf($defaultType)->yes()) {
			return [sprintf('Parameter #4 $default of method Symfony\Component\Console\Command\Command::addArgument() expects array<int, string>|null, %s given.', $defaultType->describe(VerbosityLevel::typeOnly()))];
		}

		return [];
	}

}
