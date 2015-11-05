<?php

namespace JosekiTests\FileTemplate;

use Joseki\FileTemplate\Schema;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class SchemaTranslationTest extends \Tester\TestCase
{

    public function testUndefinedVariables()
    {
        $schema = new Schema([], []);
        Assert::equal([], $schema->getUndefinedVariables());

        $schema = new Schema(['DS' => '$', 'NAMESPACE' => null], []);
        Assert::equal(['NAMESPACE'], $schema->getUndefinedVariables());

        $schema = new Schema(['DS' => '$', 'NAMESPACE' => null, 'CLASS' => null,], []);
        Assert::equal(['NAMESPACE', 'CLASS'], $schema->getUndefinedVariables());
    }



    public function testResolution()
    {
        $schema = new Schema(['CLASS' => 'Helper', 'NAME' => '${CLASS}Test', 'FILE' => '${NAME}.php',], []);
        $schema->resolveVariables();
        Assert::equal('Helper', $schema->getVariable('CLASS'));
        Assert::equal('HelperTest', $schema->getVariable('NAME'));
        Assert::equal('HelperTest.php', $schema->getVariable('FILE'));

        // reverted order
        $schema = new Schema(['FILE' => '${NAME}.php', 'NAME' => '${CLASS}Test', 'CLASS' => 'Helper',], []);
        $schema->resolveVariables();
        Assert::equal('Helper', $schema->getVariable('CLASS'));
        Assert::equal('HelperTest', $schema->getVariable('NAME'));
        Assert::equal('HelperTest.php', $schema->getVariable('FILE'));

        // resolved using translation
        $schema = new Schema(['FILE' => '${NAME}.php', 'NAME' => '${CLASS}Test', 'CLASS' => 'Helper',], []);
        Assert::equal('Helper', $schema->translate('${CLASS}'));
        Assert::equal('HelperTest', $schema->translate('${NAME}'));
        Assert::equal('HelperTest.php', $schema->translate('${FILE}'));
        Assert::equal('FooHelperTest.phpBar', $schema->translate('Foo${FILE}Bar'));
    }



    public function testGetSetters()
    {
        $schema = new Schema(['FOO' => 'foo'], []);
        Assert::equal('foo', $schema->getVariable('FOO'));
        Assert::exception(
            function () use ($schema) {
                $schema->getVariable('BAR');
            },
            'Joseki\FileTemplate\InvalidArgumentException',
            "Variable 'BAR' not found"
        );

        $schema->setVariable('BAR', 'bar');
        Assert::equal('bar', $schema->getVariable('BAR'));

        $schema->resolveVariables();
        Assert::exception(
            function () use ($schema) {
                $schema->setVariable('HELLO', 'world');
            },
            'Joseki\FileTemplate\InvalidStateException',
            'Cannot set variable \'HELLO\'. Variables has been already resolved and used in translation. This will lead into translation inconsistency.'
        );
    }



    public function testCircularSimple()
    {
        $schema = new Schema(['CLASS' => '${CLASS}',], []);
        Assert::exception(
            function () use ($schema) {
                $schema->translate('${CLASS}');
            },
            'Joseki\FileTemplate\InvalidStateException',
            "Variable 'CLASS' ('\${CLASS}') could not be resolved. Perhaps due undefined variable or circular dependencies."
        );

        $schema = new Schema(['CONTROL' => '${FACTORY}', 'FACTORY' => '${CONTROL}',], []);
        Assert::exception(
            function () use ($schema) {
                $schema->translate('${CONTROL}');
            },
            'Joseki\FileTemplate\InvalidStateException',
            "Variable 'CONTROL' ('\${FACTORY}') could not be resolved. Perhaps due undefined variable or circular dependencies."
        );

        $schema = new Schema(['FOO' => '${BAR}', 'BAR' => '${FOOBAR}', 'FOOBAR' => '${FOO}',], []);
        Assert::exception(
            function () use ($schema) {
                $schema->translate('${FOO}');
            },
            'Joseki\FileTemplate\InvalidStateException',
            "Variable 'FOO' ('\${BAR}') could not be resolved. Perhaps due undefined variable or circular dependencies."
        );
    }



    public function testDefaults()
    {
        /** @var Schema $schema */
        $schema = \Nette\PhpGenerator\Helpers::createObject('Joseki\FileTemplate\Schema', array(
            "\x00Joseki\\FileTemplate\\Schema\x00files" => array(
                'PRESENTER_FILE' => 'C:\wamp\wamp\www\Joseki\FileTemplate\tests\JosekiTests\FileTemplate/templates/module.presenter.txt',
                'HOMEPAGE_PRESENTER_FILE' => 'C:\wamp\wamp\www\Joseki\FileTemplate\tests\JosekiTests\FileTemplate/templates/module.presenter.homepage.txt',
                'TEMPLATE_FILE' => 'C:\wamp\wamp\www\Joseki\FileTemplate\tests\JosekiTests\FileTemplate/templates/module.template.txt',
                'LAYOUT_FILE' => 'C:\wamp\wamp\www\Joseki\FileTemplate\tests\JosekiTests\FileTemplate/templates/module.layout.txt',
            ),
            "\x00Joseki\\FileTemplate\\Schema\x00variables" => array(
                'NAMESPACE' => 'Demo\Application\${PARENT_MODULE}\${NAME}',
                'DS' => '$',
                'NAME' => 'Foo',
                'PARENT_MODULE' => 'Admin',
                'PRESENTER_FILE' => 'Presenter.php',
                'HOMEPAGE_PRESENTER_FILE' => 'HomepagePresenter.php',
                'TEMPLATE_FILE' => 'Homepage/default.latte',
                'LAYOUT_FILE' => '@layout.latte',
            ),
            "\x00Joseki\\FileTemplate\\Schema\x00resolved" => FALSE,
        ));

        Assert::true($schema instanceof Schema);
        Assert::equal('Foo', $schema->getVariable('NAME'));
        Assert::equal('Admin', $schema->getVariable('PARENT_MODULE'));
        Assert::equal('Demo\Application\Admin\Foo', $schema->getVariable('NAMESPACE'));
    }

}

\run(new SchemaTranslationTest());
