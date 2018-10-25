<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class ExampleController extends Controller
{

	public function services(): void
	{
		$service1 = $this->get('foo');
		$service2 = $this->get('bar');
		$service3 = $this->get(doFoo());
		$service4 = $this->get();

		$has1 = $this->has('foo');
		$has2 = $this->has('bar');
		$has3 = $this->has(doFoo());
		$has4 = $this->has();

		die;
	}

}
