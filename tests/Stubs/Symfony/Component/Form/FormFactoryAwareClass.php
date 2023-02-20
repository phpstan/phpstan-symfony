<?php

namespace Stubs\Symfony\Component\Form;

use Symfony\Component\Form\FormFactoryInterface;

class FormFactoryAwareClass
{
	/**
	 * @var FormFactoryInterface
	 */
	private $formFactory;

	public function __construct(
		FormFactoryInterface $formFactory
	) {
		$this->formFactory = $formFactory;
	}

	public function doSomething(): void
	{
		$form = $this->formFactory->create(DataClassType::class, new DataClass());
		$data = $form->getData();
		$this->thisOnlyAcceptsDataClass($data);
	}

	private function thisOnlyAcceptsDataClass(DataClass $data): void
	{
	}
}
