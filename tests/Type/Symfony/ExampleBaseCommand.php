<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ExampleBaseCommand extends Command
{

	protected function configure(): void
	{
		parent::configure();

		$this->addArgument('base');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$base = $input->getArgument('base');
		$aaa = $input->getArgument('aaa');
		$bbb = $input->getArgument('bbb');
		$diff = $input->getArgument('diff');
		$arr = $input->getArgument('arr');
		$both = $input->getArgument('both');

		die;
	}

}
