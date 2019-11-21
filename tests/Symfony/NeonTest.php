<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\TestCase;

final class NeonTest extends TestCase
{

	public function testExtensionNeon(): void
	{
		$tmpDir = __DIR__ . '/../tmp';
		$containerFactory = new ContainerFactory(__DIR__);
		$container = $containerFactory->create($tmpDir, [
			__DIR__ . '/../../extension.neon',
			__DIR__ . '/../../rules.neon',
			__DIR__ . '/config.neon',

		], []);
		$parameters = $container->getParameters();
		self::assertArrayHasKey('rootDir', $parameters);
		self::assertSame(realpath(__DIR__ . '/../../vendor/phpstan/phpstan'), $parameters['rootDir']);

		self::assertArrayHasKey('symfony', $parameters);
		self::assertSame([
			'container_xml_path' => __DIR__ . DIRECTORY_SEPARATOR . 'container.xml',
			'constant_hassers' => true,
			'console_application_loader' => null,
		], $parameters['symfony']);

		self::assertCount(6, $container->getServicesByTag('phpstan.rules.rule'));
		self::assertCount(16, $container->getServicesByTag('phpstan.broker.dynamicMethodReturnTypeExtension'));
		self::assertCount(7, $container->getServicesByTag('phpstan.typeSpecifier.methodTypeSpecifyingExtension'));
		self::assertInstanceOf(ServiceMap::class, $container->getByType(ServiceMap::class));
	}

}
