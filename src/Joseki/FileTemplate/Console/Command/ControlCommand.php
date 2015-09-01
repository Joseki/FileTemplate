<?php

namespace Joseki\Migration\Console\Command;

use Joseki\FileTemplate\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ControlCommand extends Command
{
	/** @var Schema[] */
	private $schemaList;

	/**
	 * ControlCommand constructor.
	 */
	public function __construct(array $schemaList)
	{
		parent::__construct();
		$this->schemaList = $schemaList;
	}

	/**
	 * @return \Joseki\FileTemplate\Schema[]
	 */
	public function getSchemaList()
	{
		return $this->schemaList;
	}

	protected function configure()
	{
		$this->setName('joseki:fileTemplate');
		$this->setDescription('FileTemplate generator');

		$this->addArgument(
			'name',
			InputArgument::REQUIRED,
			'Which command (set of templates) are you going to create?'
		);

		$this->addArgument(
			'dir',
			InputArgument::REQUIRED,
			'Directory for new files?'
		);
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{
//		$helper = $this->getHelper('question');
//
//		// variables
//		$question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
//		$answer = $helper->ask($input, $output, $question);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->write('Joseki FileTemplate success');
	}
}
