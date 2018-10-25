<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
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

		$loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('rules', new RulesExtension());
			$compiler->loadConfig(__DIR__ . '/config.neon');
			$compiler->loadConfig(__DIR__ . '/../../extension.neon');
		}, $key);

		self::assertFileEquals(__DIR__ . '/ExampleContainer.php', $generatedContainer);
	}

}
