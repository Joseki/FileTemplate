<?php

namespace JosekiTests\FileTemplate;

use Joseki\Console\DI\FileTemplateExtension;
use Joseki\FileTemplate\Schema;
use Joseki\Migration\Console\Command\ControlCommand;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\Utils\Random;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class CommandTest extends \Tester\TestCase
{

	private function prepareConfigurator()
	{
		$configurator = new Configurator;
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->addParameters(array('container' => array('class' => 'SystemContainer_' . Random::generate())));

		$configurator->onCompile[] = function ($configurator, Compiler $compiler) {
			$compiler->addExtension('FileTemplate', new FileTemplateExtension());
		};

		return $configurator;
	}

	public function testOneCommand()
	{
		$configurator = $this->prepareConfigurator();
		$configurator->addConfig(__DIR__ . '/config/config.one.command.neon', $configurator::NONE);

		/** @var \Nette\DI\Container $container */
		$container = $configurator->createContainer();

		/** @var ControlCommand $commandService */
		$commandService = $container->getByType('Joseki\Migration\Console\Command\ControlCommand');

		$application = new Application();
		$application->add($commandService);

		$command = $application->find('joseki:fileTemplate');
		$commandTester = new CommandTester($command);
		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'control',
			'dir' => 'output'
		]);

		Assert::match('Joseki FileTemplate success', $commandTester->getDisplay());

		// ...
	}
}

\run(new CommandTest());
