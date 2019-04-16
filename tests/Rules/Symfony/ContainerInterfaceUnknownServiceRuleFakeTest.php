<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ContainerInterfaceUnknownServiceRuleFakeTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfaceUnknownServiceRule((new XmlServiceMapFactory(null))->create(), new Standard());
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new ServiceTypeSpecifyingExtension(Controller::class, new Standard()),
		];
	}

	public function testGetPrivateService(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleController.php',
			],
			[]
		);
	}

}
