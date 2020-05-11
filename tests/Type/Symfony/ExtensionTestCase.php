<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Broker\AnonymousClassNameHelper;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\Node\VirtualNode;
use PHPStan\PhpDoc\PhpDocInheritanceResolver;
use PHPStan\PhpDoc\PhpDocNodeResolver;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\ReflectionProvider\DirectReflectionProviderProvider;
use PHPStan\Testing\TestCase;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\VerbosityLevel;

abstract class ExtensionTestCase extends TestCase
{

	protected function processFile(
		string $file,
		string $expression,
		string $type,
		DynamicMethodReturnTypeExtension $extension
	): void
	{
		$broker = $this->createBroker([$extension]);
		$parser = $this->getParser();
		$currentWorkingDirectory = $this->getCurrentWorkingDirectory();
		$fileHelper = new FileHelper($currentWorkingDirectory);
		$typeSpecifier = $this->createTypeSpecifier(new Standard(), $broker);
		/** @var \PHPStan\PhpDoc\PhpDocStringResolver $phpDocStringResolver */
		$phpDocStringResolver = self::getContainer()->getByType(PhpDocStringResolver::class);
		$fileTypeMapper = new FileTypeMapper(
			new DirectReflectionProviderProvider($broker),
			$parser,
			$phpDocStringResolver,
			self::getContainer()->getByType(PhpDocNodeResolver::class),
			$this->createMock(Cache::class),
			$this->createMock(AnonymousClassNameHelper::class)
		);
		$resolver = new NodeScopeResolver(
			$broker,
			$parser,
			$fileTypeMapper,
			new PhpDocInheritanceResolver($fileTypeMapper),
			$fileHelper,
			$typeSpecifier,
			true,
			true,
			true,
			[],
			[]
		);
		$resolver->setAnalysedFiles([$fileHelper->normalizePath($file)]);

		$run = false;
		$resolver->processNodes(
			$parser->parseFile($file),
			$this->createScopeFactory($broker, $typeSpecifier)->create(ScopeContext::create($file)),
			function (Node $node, Scope $scope) use ($expression, $type, &$run): void {
				if ($node instanceof VirtualNode) {
					return;
				}
				if ((new Standard())->prettyPrint([$node]) !== 'die') {
					return;
				}
				/** @var \PhpParser\Node\Stmt\Expression $expNode */
				$expNode = $this->getParser()->parseString(sprintf('<?php %s;', $expression))[0];
				self::assertSame($type, $scope->getType($expNode->expr)->describe(VerbosityLevel::typeOnly()));
				$run = true;
			}
		);
		self::assertTrue($run);
	}

}
