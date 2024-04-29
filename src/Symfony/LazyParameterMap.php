<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

class LazyParameterMap implements ParameterMap
{

	private ParameterMapFactory $factory;

	private ?ParameterMap $parameterMap = null;

	public function __construct(ParameterMapFactory $factory)
	{
		$this->factory = $factory;
	}

	public function getParameters(): array
	{
		return ($this->parameterMap ??= $this->factory->create())->getParameters();
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return ($this->parameterMap ??= $this->factory->create())->getParameter($key);
	}

	public function getParameterKeysFromNode(Expr $node, Scope $scope): array
	{
		return ($this->parameterMap ??= $this->factory->create())->getParameterKeysFromNode($node, $scope);
	}

}
