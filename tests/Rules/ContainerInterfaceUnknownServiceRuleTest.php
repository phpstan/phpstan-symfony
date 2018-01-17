<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Rules;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Rules\Rule;

final class ContainerInterfaceUnknownServiceRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): Rule
	{
		$serviceMap = new ServiceMap(__DIR__ . '/../container.xml');

		return new ContainerInterfaceUnknownServiceRule($serviceMap);
	}

	public function testGetUnknownService()
	{
		$this->analyse([__DIR__ . '/data/ExampleController.php'], [
			[
				'Service "service.not.found" is not registered in the container.',
				21,
			],
			[
				'Service "Lookyman\PHPStan\Symfony\ServiceMap" is not registered in the container.',
				27,
			],
		]);
	}

}
