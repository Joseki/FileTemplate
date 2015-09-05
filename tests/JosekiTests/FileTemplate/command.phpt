<?php

namespace JosekiTests\FileTemplate;

use Joseki\FileTemplate\DI\FileTemplateExtension;
use Joseki\FileTemplate\Console\Command\ControlCommand;
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
        $commandService = $container->getByType('Joseki\FileTemplate\Console\Command\ControlCommand');

        $application = new Application();
        $application->add($commandService);

        $command = $application->find('joseki:file-template');
        $commandTester = new CommandTester($command);

        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream(sprintf('Foo%sBar%s', PHP_EOL, PHP_EOL)));

        $commandTester->execute(
            [
                'command' => $command->getName(),
                'name' => 'example1',
                'dir' => 'output'
            ]
        );

        Assert::match('#Created file:[^\n]*Foo.php#', $commandTester->getDisplay());
        Assert::match('#Created file:[^\n]*FooFactory.php#', $commandTester->getDisplay());
        Assert::match('#Created file:[^\n]*template.latte#', $commandTester->getDisplay());

        $this->assertFiles(__DIR__ . '/files/expected.Foo.php', __DIR__ . '/output/Foo.php');
        $this->assertFiles(__DIR__ . '/files/expected.FooFactory.php', __DIR__ . '/output/FooFactory.php');
        $this->assertFiles(__DIR__ . '/files/expected.template.latte', __DIR__ . '/output/template.latte');
    }



    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }



    protected function assertFiles($expected, $actual)
    {
        Assert::true(file_exists($expected));
        Assert::true(file_exists($actual));
        Assert::equal(file_get_contents($expected), file_get_contents($actual));
    }
}

\run(new CommandTest());
