<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
use PHPStan\DependencyInjection\ParametersSchemaExtension;
use PHPStan\DependencyInjection\RulesExtension;
use PHPUnit\Framework\TestCase;
use function sprintf;
use function unlink;

final class NeonTest extends TestCase
{

	public function testExtensionNeon(): void
	{
		$key = '';
		$tmpDir = __DIR__ . '/../tmp';
		$loader = new ContainerLoader($tmpDir, true);
		$generatedContainer = sprintf('%s/%s.php', $tmpDir, $loader->getClassName($key));

		@unlink($generatedContainer);
		self::assertFileNotExists($generatedContainer);

		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('rules', new RulesExtension());
			$compiler->addExtension('parametersSchema', new ParametersSchemaExtension());
			$compiler->addConfig(['parameters' => ['rootDir' => __DIR__]]);
			$compiler->loadConfig(__DIR__ . '/../../extension.neon');
			$compiler->loadConfig(__DIR__ . '/config.neon');
		}, $key);
		/** @var \Nette\DI\Container $container */
		$container = new $class();

		$parameters = $container->getParameters();
		unset($parameters['__parametersSchema']);

		self::assertSame([
			'rootDir' => __DIR__,
			'symfony' => [
				'container_xml_path' => __DIR__ . '/container.xml',
				'constant_hassers' => true,
				'console_application_loader' => null,
			],
		], $parameters);

		self::assertCount(6, $container->findByTag('phpstan.rules.rule'));
		self::assertCount(11, $container->findByTag('phpstan.broker.dynamicMethodReturnTypeExtension'));
		self::assertCount(5, $container->findByTag('phpstan.typeSpecifier.methodTypeSpecifyingExtension'));
		self::assertInstanceOf(ServiceMap::class, $container->getByType(ServiceMap::class));
	}

}
