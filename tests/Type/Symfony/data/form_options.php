<?php declare(strict_types = 1);

namespace GenericFormOptionsType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function PHPStan\Testing\assertType;

class DataClass
{
}

/**
 * @extends AbstractType<DataClass, array{required: string, optional: int}>
 */
class DataClassType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		assertType('string', $options['required']);
		assertType('int', $options['optional']);

		$builder
			->add('foo', NumberType::class)
			->add('bar', TextType::class)
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver
			->setDefaults([
				'data_class' => DataClass::class,
				'optional' => 0,
			])
			->setRequired('required')
			->setAllowedTypes('required', 'string')
			->setAllowedTypes('optional', 'int')
		;
	}

}

class FormFactoryAwareClass
{

	/** @var FormFactoryInterface */
	private $formFactory;

	public function __construct(FormFactoryInterface $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function doSomething(): void
	{
		$form = $this->formFactory->create(DataClassType::class, new DataClass());
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

	public function doSomethingWithOption(): void
	{
		$form = $this->formFactory->create(DataClassType::class, new DataClass(), ['required' => 'foo']);
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

	public function doSomethingWithInvalidOption(): void
	{
		$form = $this->formFactory->create(DataClassType::class, new DataClass(), ['required' => 42]);
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

}

class FormController extends AbstractController
{

	public function doSomething(): void
	{
		$form = $this->createForm(DataClassType::class, new DataClass());
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

	public function doSomethingWithOption(): void
	{
		$form = $this->createForm(DataClassType::class, new DataClass(), ['required' => 'foo']);
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

	public function doSomethingWithInvalidOption(): void
	{
		$form = $this->createForm(DataClassType::class, new DataClass(), ['required' => 42]);
		assertType('Symfony\Component\Form\FormInterface<GenericFormOptionsType\DataClass>', $form);
	}

}
