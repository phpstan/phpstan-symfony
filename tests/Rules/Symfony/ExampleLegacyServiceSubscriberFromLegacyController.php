<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

final class ExampleLegacyServiceSubscriberFromLegacyController extends Controller implements ServiceSubscriberInterface
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
