<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
		$this->analyse([__DIR__ . '/request_get_session.php'], [
			[
				'Variable $session1 is: ' . SessionInterface::class . '|null',
				7,
			],
			[
				'Variable $session2 is: ' . SessionInterface::class,
				11,
			],
		]);
	}

}
