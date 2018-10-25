<?php
class Container_14d7103555 extends Nette\DI\Container
{
	protected $meta = [
		'types' => [
			'PHPStan\Rules\Rule' => [['rules.0', 'rules.1']],
			'PHPStan\Rules\Symfony\ContainerInterfacePrivateServiceRule' => [['rules.0']],
			'PHPStan\Rules\Symfony\ContainerInterfaceUnknownServiceRule' => [['rules.1']],
			'PhpParser\PrettyPrinterAbstract' => [1 => ['3_PhpParser_PrettyPrinter_Standard']],
			'PhpParser\PrettyPrinter\Standard' => [1 => ['3_PhpParser_PrettyPrinter_Standard']],
			'PHPStan\Symfony\ServiceMap' => [1 => ['4']],
			'PHPStan\Symfony\ServiceMapFactory' => [1 => ['symfony.serviceMapFactory']],
			'PHPStan\Type\DynamicMethodReturnTypeExtension' => [1 => ['6', '7', '9', '11']],
			'PHPStan\Type\Symfony\RequestDynamicReturnTypeExtension' => [1 => ['6']],
			'PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension' => [1 => ['7', '9', '11']],
			'PHPStan\Type\MethodTypeSpecifyingExtension' => [1 => ['8', '10', '12']],
			'PHPStan\Analyser\TypeSpecifierAwareExtension' => [1 => ['8', '10', '12']],
			'PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension' => [1 => ['8', '10', '12']],
			'Nette\DI\Container' => [1 => ['container']],
		],
		'services' => [
			'3_PhpParser_PrettyPrinter_Standard' => 'PhpParser\PrettyPrinter\Standard',
			'container' => 'Nette\DI\Container',
			'rules.0' => 'PHPStan\Rules\Symfony\ContainerInterfacePrivateServiceRule',
			'rules.1' => 'PHPStan\Rules\Symfony\ContainerInterfaceUnknownServiceRule',
			'symfony.serviceMapFactory' => 'PHPStan\Symfony\ServiceMapFactory',
			4 => 'PHPStan\Symfony\ServiceMap',
			6 => 'PHPStan\Type\Symfony\RequestDynamicReturnTypeExtension',
			'PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension',
			'PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension',
			'PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension',
			'PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension',
			'PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension',
			'PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension',
		],
		'tags' => [
			'phpstan.rules.rule' => ['rules.0' => true, 'rules.1' => true],
			'phpstan.broker.dynamicMethodReturnTypeExtension' => [6 => true, true, 9 => true, 11 => true],
			'phpstan.typeSpecifier.methodTypeSpecifyingExtension' => [8 => true, 10 => true, 12 => true],
		],
		'aliases' => [],
	];


	public function __construct(array $params = [])
	{
		$this->parameters = $params;
		$this->parameters += ['symfony' => ['container_xml_path' => '']];
	}


	public function createService__3_PhpParser_PrettyPrinter_Standard(): PhpParser\PrettyPrinter\Standard
	{
		$service = new PhpParser\PrettyPrinter\Standard;
		return $service;
	}


	public function createServiceContainer(): Nette\DI\Container
	{
		return $this;
	}


	public function createServiceRules__0(): PHPStan\Rules\Symfony\ContainerInterfacePrivateServiceRule
	{
		$service = new PHPStan\Rules\Symfony\ContainerInterfacePrivateServiceRule($this->getService('4'));
		return $service;
	}


	public function createServiceRules__1(): PHPStan\Rules\Symfony\ContainerInterfaceUnknownServiceRule
	{
		$service = new PHPStan\Rules\Symfony\ContainerInterfaceUnknownServiceRule($this->getService('4'), $this->getService('3_PhpParser_PrettyPrinter_Standard'));
		return $service;
	}


	public function createServiceSymfony__serviceMapFactory(): PHPStan\Symfony\ServiceMapFactory
	{
		$service = new PHPStan\Symfony\XmlServiceMapFactory('');
		return $service;
	}


	public function createService__4(): PHPStan\Symfony\ServiceMap
	{
		$service = new PHPStan\Symfony\ServiceMap($this->getService('symfony.serviceMapFactory')->create());
		return $service;
	}


	public function createService__6(): PHPStan\Type\Symfony\RequestDynamicReturnTypeExtension
	{
		$service = new PHPStan\Type\Symfony\RequestDynamicReturnTypeExtension;
		return $service;
	}


	public function createService__7(): PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', $this->getService('4'));
		return $service;
	}


	public function createService__8(): PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension(
			'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
			$this->getService('3_PhpParser_PrettyPrinter_Standard')
		);
		return $service;
	}


	public function createService__9(): PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension('Symfony\Bundle\FrameworkBundle\Controller\Controller', $this->getService('4'));
		return $service;
	}


	public function createService__10(): PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension(
			'Symfony\Bundle\FrameworkBundle\Controller\Controller',
			$this->getService('3_PhpParser_PrettyPrinter_Standard')
		);
		return $service;
	}


	public function createService__11(): PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceDynamicReturnTypeExtension('Symfony\Component\DependencyInjection\ContainerInterface', $this->getService('4'));
		return $service;
	}


	public function createService__12(): PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension
	{
		$service = new PHPStan\Type\Symfony\ServiceTypeSpecifyingExtension(
			'Symfony\Component\DependencyInjection\ContainerInterface',
			$this->getService('3_PhpParser_PrettyPrinter_Standard')
		);
		return $service;
	}


	public function initialize()
	{
	}
}
