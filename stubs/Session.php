<?php

namespace Symfony\Component\HttpFoundation\Session;

/**
 * @implements \IteratorAggregate<string, mixed>
 */
class Session implements \IteratorAggregate
{

	/**
	 * @phpstan-return \Traversable<string, mixed>
	 */
	public function getIterator() {}

}
