<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Symfony\Component\Console\Input\InputOption;
use function method_exists;

class GetOptionTypeHelper
{

	public function getOptionType(Scope $scope, InputOption $option): Type
	{
		if (!$option->acceptValue()) {
			if (method_exists($option, 'isNegatable') && $option->isNegatable()) {
				return new UnionType([new BooleanType(), new NullType()]);
			}

			return new BooleanType();
		}

		$optType = TypeCombinator::union(new StringType(), new NullType());
		if ($option->isValueRequired() && ($option->isArray() || $option->getDefault() !== null)) {
			$optType = TypeCombinator::removeNull($optType);
		}
		if ($option->isArray()) {
			$optType = new ArrayType(new IntegerType(), $optType);
		}

		return TypeCombinator::union($optType, $scope->getTypeFromValue($option->getDefault()));
	}

}
