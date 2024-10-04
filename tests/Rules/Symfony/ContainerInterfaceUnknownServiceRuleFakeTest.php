<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\XmlServiceMapFactory;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use function class_exists;

/**
 * @extends RuleTestCase<ContainerInterfaceUnknownServiceRule>
 */
final class ContainerInterfaceUnknownServiceRuleFakeTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new ContainerInterfaceUnknownServiceRule((new XmlServiceMapFactory(new Configuration([])))->create(), self::getContainer()->getByType(Printer::class));
	}

	/**
	 * @return MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new ServiceTypeSpecifyingExtension(AbstractController::class, self::getContainer()->getByType(Printer::class)),
		];
	}

	public function testGetPrivateService(): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
		}
		$this->analyse(
			[
				__DIR__ . '/ExampleController.php',
			],
			[],
		);
	}

	public function testGetPrivateServiceInAbstractController(): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
			self::markTestSkipped();
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleAbstractController.php',
			],
			[],
		);
	}

}
