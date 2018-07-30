<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\Symfony\ContainerInterfaceMethodTypeSpecifyingExtension;
use PHPStan\Type\Symfony\ControllerMethodTypeSpecifyingExtension;

final class ContainerInterfaceUnknownServiceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$serviceMap = new ServiceMap(__DIR__ . '/../../Symfony/data/container.xml');

		return new ContainerInterfaceUnknownServiceRule($serviceMap, new Standard());
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new ContainerInterfaceMethodTypeSpecifyingExtension(new Standard()),
			new ControllerMethodTypeSpecifyingExtension(new Standard()),
		];
	}

	public function testGetUnknownService(): void
	{
		$this->analyse(
			[__DIR__ . '/../../Symfony/data/ExampleController.php'],
			[
				[
					'Service "service.not.found" is not registered in the container.',
					21,
				],
				[
					'Service "PHPStan\Symfony\ServiceMap" is not registered in the container.',
					27,
				],
				[
					'Service "service.PHPStan\Symfony\ServiceMap" is not registered in the container.',
					39,
				],
				[
					'Service "PHPStan\Symfony\ExampleController" is not registered in the container.',
					45,
				],
			]
		);
	}

}
