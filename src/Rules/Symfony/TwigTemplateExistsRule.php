<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use function count;
use function file_exists;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
final class TwigTemplateExistsRule implements Rule
{

	/** @var array<string, string|null> */
	private $twigTemplateDirectories;

	/** @param array<string, string|null> $twigTemplateDirectories */
	public function __construct(array $twigTemplateDirectories)
	{
		$this->twigTemplateDirectories = $twigTemplateDirectories;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (count($this->twigTemplateDirectories) === 0) {
			return [];
		}

		$templateArg = $this->getTwigTemplateArg($node, $scope);

		if ($templateArg === null) {
			return [];
		}

		$templateNames = [];

		if ($templateArg->value instanceof Variable && is_string($templateArg->value->name)) {
			$varType = $scope->getVariableType($templateArg->value->name);

			foreach ($varType->getConstantStrings() as $constantString) {
				$templateNames[] = $constantString->getValue();
			}
		} elseif ($templateArg->value instanceof String_) {
			$templateNames[] = $templateArg->value->value;
		}

		if (count($templateNames) === 0) {
			return [];
		}

		$errors = [];

		foreach ($templateNames as $templateName) {
			if ($this->twigTemplateExists($templateName)) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Twig template "%s" does not exist.',
				$templateName
			))->line($templateArg->getStartLine())->identifier('twig.templateNotFound')->build();
		}

		return $errors;
	}

	private function getTwigTemplateArg(MethodCall $node, Scope $scope): ?Arg
	{
		if (!$node->name instanceof Identifier) {
			return null;
		}

		$argType = $scope->getType($node->var);
		$methodName = $node->name->name;

		if ((new ObjectType('Symfony\Bundle\FrameworkBundle\Controller\AbstractController'))->isSuperTypeOf($argType)->yes() && in_array($methodName, ['render', 'renderView', 'renderBlockView', 'renderBlock', 'renderForm', 'stream'], true)) {
			return $node->getArgs()[0] ?? null;
		}

		if ((new ObjectType('Twig\Environment'))->isSuperTypeOf($argType)->yes() && in_array($methodName, ['render', 'display', 'load'], true)) {
			return $node->getArgs()[0] ?? null;
		}

		if ((new ObjectType('Symfony\Bridge\Twig\Mime\TemplatedEmail'))->isSuperTypeOf($argType)->yes() && in_array($methodName, ['htmlTemplate', 'textTemplate'], true)) {
			return $node->getArgs()[0] ?? null;
		}

		return null;
	}

	private function twigTemplateExists(string $templateName): bool
	{
		if (preg_match('#^@(.+)\/(.+)$#', $templateName, $matches) === 1) {
			$templateNamespace = $matches[1];
			$templateName = $matches[2];
		} else {
			$templateNamespace = null;
		}

		foreach ($this->twigTemplateDirectories as $twigTemplateDirectory => $namespace) {
			if ($namespace !== $templateNamespace) {
				continue;
			}

			$templatePath = $twigTemplateDirectory . '/' . $templateName;

			if (file_exists($templatePath)) {
				return true;
			}
		}

		return false;
	}

}
