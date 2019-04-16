<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ExampleOptionCommand extends Command
{

	protected function configure(): void
	{
		parent::configure();
		$this->setName('example-option');

		$this->addOption('a', null, InputOption::VALUE_NONE);
		$this->addOption('b', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('c', null, InputOption::VALUE_REQUIRED);
		$this->addOption('d', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL);
		$this->addOption('e', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED);

		$this->addOption('bb', null, InputOption::VALUE_OPTIONAL, '', 1);
		$this->addOption('cc', null, InputOption::VALUE_REQUIRED, '', 1);
		$this->addOption('dd', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '', [1]);
		$this->addOption('ee', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, '', [1]);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$a = $input->getOption('a');
		$b = $input->getOption('b');
		$c = $input->getOption('c');
		$d = $input->getOption('d');
		$e = $input->getOption('e');

		$bb = $input->getOption('bb');
		$cc = $input->getOption('cc');
		$dd = $input->getOption('dd');
		$ee = $input->getOption('ee');

		die;
	}

}
