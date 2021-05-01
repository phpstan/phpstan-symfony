<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ExampleAbstractControllerWithoutContainer extends AbstractController
{

	public function services(): void
	{
		assertType('object', $this->get('foo'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('bool', $this->has('foo'));
		assertType('bool', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

	public function parameters(ParameterBagInterface $parameterBag): void
	{
		assertType('array|bool|float|int|string|null', $parameterBag->get('unknown'));
		assertType('array|bool|float|int|string|null', $this->getParameter('unknown'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.string'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.string'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.int'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.int'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.int_as_string'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.int_as_string'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.float'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.float'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.float_as_string'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.float_as_string'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.boolean'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.boolean'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.boolean_as_string'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.boolean_as_string'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.list'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.list'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.list_of_list'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.list_of_list'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.map'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.map'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.binary'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.binary'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('app.constant'));
		assertType('array|bool|float|int|string|null', $this->getParameter('app.constant'));

		assertType('bool', $parameterBag->has('unknown'));
		assertType('bool', $parameterBag->has('app.string'));
		assertType('bool', $parameterBag->has('app.int'));
		assertType('bool', $parameterBag->has('app.int_as_string'));
		assertType('bool', $parameterBag->has('app.float'));
		assertType('bool', $parameterBag->has('app.float_as_string'));
		assertType('bool', $parameterBag->has('app.boolean'));
		assertType('bool', $parameterBag->has('app.boolean_as_string'));
		assertType('bool', $parameterBag->has('app.list'));
		assertType('bool', $parameterBag->has('app.list_of_list'));
		assertType('bool', $parameterBag->has('app.map'));
		assertType('bool', $parameterBag->has('app.binary'));
		assertType('bool', $parameterBag->has('app.constant'));
	}

}
