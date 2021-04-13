<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper;
use PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\MethodTypeSpecifyingExtension;

/**
 * @extends RuleTestCase<\PHPStan\Rules\Comparison\ImpossibleCheckTypeMethodCallRule>
 */
final class RequestTypeSpecifyingExtensionImpossibleTest extends RuleTestCase
{

	/** @var \PHPStan\Broker\Broker|null */
	private $broker;

	/** @var \PHPStan\Type\MethodTypeSpecifyingExtension|null */
	private $extension;

	private function getBroker(): Broker
	{
		if ($this->broker === null) {
			$this->broker = $this->createBroker();
		}
		return $this->broker;
	}

	private function getExtension(): MethodTypeSpecifyingExtension
	{
		if ($this->extension === null) {
			$this->extension = new RequestTypeSpecifyingExtension($this->getBroker(), new Standard());
		}
		return $this->extension;
	}

	protected function getRule(): Rule
	{
		return new ImpossibleCheckTypeMethodCallRule(new ImpossibleCheckTypeHelper(
			$this->getBroker(),
			$this->createTypeSpecifier(new Standard(), $this->getBroker(), $this->getMethodTypeSpecifyingExtensions(), []),
			[],
			false
		), true, false);
	}

	/** @return MethodTypeSpecifyingExtension[] */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			$this->getExtension(),
		];
	}

	public function testGetSession(): void
	{
		$this->analyse([__DIR__ . '/request_get_session.php'], []);
	}

}
