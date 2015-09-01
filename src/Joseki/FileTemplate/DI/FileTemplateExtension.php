<?php

namespace Joseki\FileTemplate\DI;

use Joseki\FileTemplate\InvalidArgumentException;
use Joseki\FileTemplate\Schema;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

class FileTemplateExtension extends CompilerExtension
{

    const TAG_JOSEKI_COMMAND = 'joseki.console.command';
    const TAG_KDYBY_COMMAND = 'kdyby.console.command';

    public $defaults = [
        'commands' => [],// prikaz pro generovani - obsahuje pole cest k sablonam
        'rootDir' => null,
    ];



    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        Validators::assert($config['commands'], 'array');

        $schemaList = [];
        foreach ($config['commands'] as $name => $details) {
            $schemaList[$name] = $this->createSchema($name, $details);
        }

        if (!$config['rootDir']) {
            $config['rootDir'] = $container->expand('%appDir%');
        }

        $container->addDefinition($this->prefix('controlCommand'))
            ->setClass('Joseki\FileTemplate\Console\Command\ControlCommand', [$schemaList, $config['rootDir']])
            ->addTag('joseki.console.command')
            ->addTag('kdyby.console.command');

    }



    private function createSchema($name, $command)
    {
        $variables = [];
        $defaults = [];

        Validators::assert($command['variables'], 'array');
        Validators::assert($command['templates'], 'array');
        if (isset($command['defaults'])) {
            Validators::assert($command['defaults'], 'array');
            $defaults = $command['defaults'];
        }

        foreach ($command['variables'] as $var) {
            $variables["$$var$"] = '';
        }

        foreach ($defaults as $varName => $value) {
            $variables["$$varName$"] = $value;
        }

        foreach ($command['templates'] as $templateVar => $template) {
            if (!file_exists($template)) {
                throw new InvalidArgumentException("Template file '$template' used in FileTemplate command '$name' not found.");
            }
            if (!array_key_exists("$$templateVar$", $variables)) {
                throw new InvalidArgumentException(
                    "Missing variable '$templateVar' in FileTemplate command '$name'. Templates must be in 'FILE_NAME_VAR: path/to/template' notation."
                );
            }
        }

        return new Schema($variables, $command['templates']);
    }

}
