<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ExampleServiceSubscriberFromAbstractController extends AbstractController
{

	public function privateService(): void
	{
		$this->get('private');
	}

	/**
	 * @return string[]
	 */
	public static function getSubscribedServices(): array
	{
		return [];
	}

}
