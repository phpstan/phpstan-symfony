<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function PHPStan\Testing\assertType;

final class ExampleAbstractController extends AbstractController
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

	public function parameters(ParameterBagInterface $parameterBag): void
	{
		assertType('mixed', $parameterBag->get('unknown'));
		//assertType('mixed', $this->getParameter('unknown'));
		assertType("'abcdef'", $parameterBag->get('app.string'));
		//assertType("'abcdef'", $this->getParameter('app.string'));
		assertType('123', $parameterBag->get('app.int'));
		//assertType('123', $this->getParameter('app.int'));
		assertType("'123'", $parameterBag->get('app.int_as_string'));
		//assertType("'123'", $this->getParameter('app.int_as_string'));
		assertType('123.45', $parameterBag->get('app.float'));
		//assertType('123.45', $this->getParameter('app.float'));
		assertType("'123.45'", $parameterBag->get('app.float_as_string'));
		//assertType("'123.45'", $this->getParameter('app.float_as_string'));
		assertType('true', $parameterBag->get('app.boolean'));
		//assertType('true', $this->getParameter('app.boolean'));
		assertType("'true'", $parameterBag->get('app.boolean_as_string'));
		//assertType("'true'", $this->getParameter('app.boolean_as_string'));
		assertType("array('en', 'es', 'fr')", $parameterBag->get('app.list'));
		//assertType("array('en', 'es', 'fr')", $this->getParameter('app.list'));
		assertType("array(array('name' => 'the name', 'value' => 'the value'), array('name' => 'another name', 'value' => 'another value'))", $parameterBag->get('app.list_of_list'));
		//assertType("array(array('name' => 'the name', 'value' => 'the value'), array('name' => 'another name', 'value' => 'another value'))", $this->getParameter('app.list_of_list'));
		assertType("array('a' => 'value of a', 'b' => 'value of b', 'c' => 'value of c')", $parameterBag->get('app.map'));
		//assertType("array('a' => 'value of a', 'b' => 'value of b', 'c' => 'value of c')", $this->getParameter('app.map'));
		assertType("'This is a Bell char '", $parameterBag->get('app.binary'));
		//assertType("'This is a Bell char '", $this->getParameter('app.binary'));
		assertType("'Y-m-d\\\\TH:i:sP'", $parameterBag->get('app.constant'));
		//assertType("'Y-m-d\\\\TH:i:sP'", $this->getParameter('app.constant'));

		assertType('false', $parameterBag->has('unknown'));
		assertType('true', $parameterBag->has('app.string'));
		assertType('true', $parameterBag->has('app.int'));
		assertType('true', $parameterBag->has('app.int_as_string'));
		assertType('true', $parameterBag->has('app.float'));
		assertType('true', $parameterBag->has('app.float_as_string'));
		assertType('true', $parameterBag->has('app.boolean'));
		assertType('true', $parameterBag->has('app.boolean_as_string'));
		assertType('true', $parameterBag->has('app.list'));
		assertType('true', $parameterBag->has('app.list_of_list'));
		assertType('true', $parameterBag->has('app.map'));
		assertType('true', $parameterBag->has('app.binary'));
		assertType('true', $parameterBag->has('app.constant'));
	}

}
