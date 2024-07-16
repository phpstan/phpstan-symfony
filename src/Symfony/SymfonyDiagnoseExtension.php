<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Command\Output;
use PHPStan\Diagnose\DiagnoseExtension;
use function sprintf;

class SymfonyDiagnoseExtension implements DiagnoseExtension
{

	/** @var ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
	}

	public function print(Output $output): void
	{
		$output->writeLineFormatted(sprintf(
			'<info>Symfony\'s consoleApplicationLoader:</info> %s',
			$this->consoleApplicationResolver->hasConsoleApplicationLoader() ? 'In use' : 'No'
		));
		$output->writeLineFormatted('');
	}

}
