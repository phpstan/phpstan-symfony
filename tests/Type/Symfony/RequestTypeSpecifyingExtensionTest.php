<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function strpos;

/**
 * @extends RuleTestCase<VariableTypeReportingRule>
 */
final class RequestTypeSpecifyingExtensionTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new VariableTypeReportingRule();
	}

	/** @return MethodTypeSpecifyingExtension[] */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new RequestTypeSpecifyingExtension($this->createBroker()),
		];
	}

	public function testGetSession(): void
	{
		$ref = new ReflectionMethod(Request::class, 'getSession');
		$doc = (string) $ref->getDocComment();

		$checkedTypeString = SessionInterface::class;
		if (strpos($doc, '@return SessionInterface|null') !== false) {
			$checkedTypeString .= '|null';
		}

		$this->analyse([__DIR__ . '/request_get_session.php'], [
			[
				'Variable $session1 is: ' . $checkedTypeString,
				7,
			],
			[
				'Variable $session2 is: ' . SessionInterface::class,
				11,
			],
		]);
	}

}
