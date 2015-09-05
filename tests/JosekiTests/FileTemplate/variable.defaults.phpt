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

class VariableDefaultsTest extends \Tester\TestCase
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



    public function testConfig()
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

\run(new VariableDefaultsTest());
