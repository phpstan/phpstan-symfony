<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function PHPStan\Testing\assertType;

final class ExampleAbstractControllerServicesWithoutContainer extends AbstractController
{

	public function services(): void
	{
		assertType('object', $this->get('foo'));
		assertType('object', $this->get('synthetic'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('bool', $this->has('foo'));
		assertType('bool', $this->has('synthetic'));
		assertType('bool', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

}
