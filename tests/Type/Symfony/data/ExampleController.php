<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function PHPStan\Testing\assertType;

final class ExampleController extends Controller
{

	public function services(): void
	{
		assertType('Foo', $this->get('foo'));
		assertType('object', $this->get('bar'));
		assertType('object', $this->get(doFoo()));
		assertType('object', $this->get());

		assertType('true', $this->has('foo'));
		assertType('false', $this->has('bar'));
		assertType('bool', $this->has(doFoo()));
		assertType('bool', $this->has());
	}

	public function parameters(ContainerInterface $container, ParameterBagInterface $parameterBag): void
	{
		assertType('array|bool|float|int|string|null', $container->getParameter('unknown'));
		assertType('array|bool|float|int|string|null', $parameterBag->get('unknown'));
		assertType('array|bool|float|int|string|null', $this->getParameter('unknown'));
		assertType("string", $container->getParameter('app.string'));
		assertType("string", $parameterBag->get('app.string'));
		assertType("string", $this->getParameter('app.string'));
		assertType('int', $container->getParameter('app.int'));
		assertType('int', $parameterBag->get('app.int'));
		assertType('int', $this->getParameter('app.int'));
		assertType("string", $container->getParameter('app.int_as_string'));
		assertType("string", $parameterBag->get('app.int_as_string'));
		assertType("string", $this->getParameter('app.int_as_string'));
		assertType('float', $container->getParameter('app.float'));
		assertType('float', $parameterBag->get('app.float'));
		assertType('float', $this->getParameter('app.float'));
		assertType("string", $container->getParameter('app.float_as_string'));
		assertType("string", $parameterBag->get('app.float_as_string'));
		assertType("string", $this->getParameter('app.float_as_string'));
		assertType('bool', $container->getParameter('app.boolean'));
		assertType('bool', $parameterBag->get('app.boolean'));
		assertType('bool', $this->getParameter('app.boolean'));
		assertType("string", $container->getParameter('app.boolean_as_string'));
		assertType("string", $parameterBag->get('app.boolean_as_string'));
		assertType("string", $this->getParameter('app.boolean_as_string'));
		assertType("array<int, string>&nonEmpty", $container->getParameter('app.list'));
		assertType("array<int, string>&nonEmpty", $parameterBag->get('app.list'));
		assertType("array<int, string>&nonEmpty", $this->getParameter('app.list'));
		assertType("array<int, array<string, string>&nonEmpty>&nonEmpty", $container->getParameter('app.list_of_list'));
		assertType("array<int, array<string, string>&nonEmpty>&nonEmpty", $parameterBag->get('app.list_of_list'));
		assertType("array<int, array<string, string>&nonEmpty>&nonEmpty", $this->getParameter('app.list_of_list'));
		assertType("array<string, string>&nonEmpty", $container->getParameter('app.map'));
		assertType("array<string, string>&nonEmpty", $parameterBag->get('app.map'));
		assertType("array<string, string>&nonEmpty", $this->getParameter('app.map'));
		assertType("string", $container->getParameter('app.binary'));
		assertType("string", $parameterBag->get('app.binary'));
		assertType("string", $this->getParameter('app.binary'));
		assertType("string", $container->getParameter('app.constant'));
		assertType("string", $parameterBag->get('app.constant'));
		assertType("string", $this->getParameter('app.constant'));

		assertType('false', $container->hasParameter('unknown'));
		assertType('false', $parameterBag->has('unknown'));
		assertType('true', $container->hasParameter('app.string'));
		assertType('true', $parameterBag->has('app.string'));
		assertType('true', $container->hasParameter('app.int'));
		assertType('true', $parameterBag->has('app.int'));
		assertType('true', $container->hasParameter('app.int_as_string'));
		assertType('true', $parameterBag->has('app.int_as_string'));
		assertType('true', $container->hasParameter('app.float'));
		assertType('true', $parameterBag->has('app.float'));
		assertType('true', $container->hasParameter('app.float_as_string'));
		assertType('true', $parameterBag->has('app.float_as_string'));
		assertType('true', $container->hasParameter('app.boolean'));
		assertType('true', $parameterBag->has('app.boolean'));
		assertType('true', $container->hasParameter('app.boolean_as_string'));
		assertType('true', $parameterBag->has('app.boolean_as_string'));
		assertType('true', $container->hasParameter('app.list'));
		assertType('true', $parameterBag->has('app.list'));
		assertType('true', $container->hasParameter('app.list_of_list'));
		assertType('true', $parameterBag->has('app.list_of_list'));
		assertType('true', $container->hasParameter('app.map'));
		assertType('true', $parameterBag->has('app.map'));
		assertType('true', $container->hasParameter('app.binary'));
		assertType('true', $parameterBag->has('app.binary'));
		assertType('true', $container->hasParameter('app.constant'));
		assertType('true', $parameterBag->has('app.constant'));
	}

}
