<?php

namespace Bug178;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Foo extends AbstractController
{

	public function doFoo(): void
	{
		if ($this->has('sonata.media.manager.category') && $this->has('sonata.media.manager.context')) {
			// do stuff that requires both managers.
		}
	}

}
