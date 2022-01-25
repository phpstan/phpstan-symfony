<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\UrlGeneratingRoutesMap;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
final class UrlGeneratorInterfaceUnknownRouteRule implements Rule
{

	/** @var UrlGeneratingRoutesMap */
	private $urlGeneratingRoutesMap;

	public function __construct(UrlGeneratingRoutesMap $urlGeneratingRoutesMap)
	{
		$this->urlGeneratingRoutesMap = $urlGeneratingRoutesMap;
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
		}

		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if (in_array($node->name->name, ['generate', 'generateUrl'], true) === false || !isset($node->getArgs()[0])) {
			return [];
		}

		$argType = $scope->getType($node->var);
		$isControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\Controller'))->isSuperTypeOf($argType);
		$isAbstractControllerType = (new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType);
		$isUrlGeneratorInterface = (new ObjectType('Symfony\Component\Routing\Generator\UrlGeneratorInterface'))->isSuperTypeOf($argType);
		if (
			$isControllerType->no()
			&& $isAbstractControllerType->no()
			&& $isUrlGeneratorInterface->no()
		) {
			return [];
		}

		$routeName = $this->urlGeneratingRoutesMap::getRouteNameFromNode($node->getArgs()[0]->value, $scope);
		if ($routeName === null) {
			return [];
		}

		if ($this->urlGeneratingRoutesMap->hasRouteName($routeName) === false) {
			return [sprintf('Route with name "%s" does not exist.', $routeName)];
		}

		return [];
	}

}
