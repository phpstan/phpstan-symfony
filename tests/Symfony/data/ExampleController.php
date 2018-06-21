<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

final class ExampleController extends Controller
{

	public const BAR = 'bar';

	public function getPrivateServiceAction(): void
	{
		$service = $this->get('private');
		$service->noMethod();
	}

	public function getUnknownService(): void
	{
		$service = $this->get('service.not.found');
		$service->noMethod();
	}

	public function getUnknownServiceByClass(): void
	{
		$service = $this->get(ServiceMap::class);
		$service->noMethod();
	}

	public function getVariableService(string $serviceKey): void
	{
		$service = $this->get($serviceKey);
		$service->noMethod();
	}

	public function getConcatenatedService(): void
	{
		$service = $this->get('service.' . ServiceMap::class);
		$service->noMethod();
	}

	public function getSelfService(): void
	{
		$service = $this->get(self::class);
		$service->noMethod();
	}

	public function testContainer(): void
	{
		$service = $this->getTestContainer()->get('private');
		$service->noMethod();
	}

	private function getTestContainer(): TestContainer
	{
	}

}
