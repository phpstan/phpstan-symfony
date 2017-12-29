<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Rules\data;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

include __DIR__ . '/Controller.php';

final class ExampleController extends Controller
{

	public function getPrivateServiceAction()
	{
		$service = $this->get('private');
		$service->noMethod();
	}

	public function getUnknownService()
	{
		$service = $this->get('service.not.found');
		$service->noMethod();
	}

}
