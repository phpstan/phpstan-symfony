<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\PrettyPrinterAbstract;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function md5;
use function sprintf;

final class Helper
{

	public static function createMarkerNode(Expr $expr, Type $type, PrettyPrinterAbstract $printer): Expr
	{
		return new Expr\Variable(md5(sprintf(
			'%s::%s',
			$printer->prettyPrintExpr($expr),
			$type->describe(VerbosityLevel::precise())
		)));
	}

}
