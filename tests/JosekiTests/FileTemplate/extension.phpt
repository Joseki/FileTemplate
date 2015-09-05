<?php

namespace JosekiTests\FileTemplate;

use Joseki\FileTemplate\DI\FileTemplateExtension;
use Joseki\FileTemplate\Schema;
use Joseki\FileTemplate\Console\Command\ControlCommand;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\Utils\Random;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class ExtensionTest extends \Tester\TestCase
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

        /** @var ControlCommand $command */
        $command = $container->getByType('Joseki\FileTemplate\Console\Command\ControlCommand');
        Assert::true($command instanceof ControlCommand);

        $schemaList = $command->getSchemaList();
        Assert::equal(1, count($schemaList));
        Assert::true(array_key_exists('example1', $schemaList));

        $schema = $schemaList['example1'];
        Assert::true($schema instanceof Schema);
        Assert::same(['CONTROL', 'NAMESPACE'], $schema->getUndefinedVariables());
    }



    public function testVariableDefaults()
    {
        $configurator = $this->prepareConfigurator();
        $configurator->addConfig(__DIR__ . '/config/config.variable.defaults.neon', $configurator::NONE);

        /** @var \Nette\DI\Container $container */
        $container = $configurator->createContainer();

        /** @var ControlCommand $command */
        $command = $container->getByType('Joseki\FileTemplate\Console\Command\ControlCommand');

        $schemaList = $command->getSchemaList();
        $schema = $schemaList['helper_test'];

        Assert::same(['CLASS', 'NAMESPACE'], $schema->getUndefinedVariables());
        Assert::equal('', $schema->getVariable('CLASS'));
    }
}

\run(new ExtensionTest());
