<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

final class ExampleLegacyServiceSubscriberFromAbstractController extends AbstractController implements ServiceSubscriberInterface
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
