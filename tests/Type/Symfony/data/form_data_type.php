<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function PHPStan\Testing\assertType;

class DataClass
{

	/** @var int */
	public $foo;

	/** @var string */
	public $bar;

}

/**
 * @extends AbstractType<DataClass>
 */
class DataClassType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
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
			])
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
		assertType('PHPStan\Type\Symfony\DataClass', $form->getData());
	}

	public function doSomethingNullable(): void
	{
		$form = $this->formFactory->create(DataClassType::class);
		assertType('PHPStan\Type\Symfony\DataClass|null', $form->getData());
	}

}
