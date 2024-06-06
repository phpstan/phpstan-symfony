<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function PHPStan\Testing\assertType;

abstract class ExampleBaseCommand extends Command
{

	protected function configure(): void
	{
		parent::configure();

		$this->addArgument('required', InputArgument::REQUIRED);
		$this->addArgument('base');
	}

	protected function interact(InputInterface $input, OutputInterface $output): int
	{
		assertType('string|null', $input->getArgument('base'));
		assertType('string', $input->getArgument('aaa'));
		assertType('string', $input->getArgument('bbb'));
		assertType('string|null', $input->getArgument('required'));
		assertType('array<int, string>|string', $input->getArgument('diff'));
		assertType('array<int, string>', $input->getArgument('arr'));
		assertType('string|null', $input->getArgument('both'));
		assertType('Symfony\Component\Console\Helper\QuestionHelper', $this->getHelper('question'));
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		assertType('string|null', $input->getArgument('base'));
		assertType('string', $input->getArgument('aaa'));
		assertType('string', $input->getArgument('bbb'));
		assertType('string', $input->getArgument('required'));
		assertType('array<int, string>|string', $input->getArgument('diff'));
		assertType('array<int, string>', $input->getArgument('arr'));
		assertType('string|null', $input->getArgument('both'));
		assertType('Symfony\Component\Console\Helper\QuestionHelper', $this->getHelper('question'));
	}

}
