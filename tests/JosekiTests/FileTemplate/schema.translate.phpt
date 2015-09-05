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
//
//
//
//    public function testTranslation()
//    {
//        $schema = new Schema(
//            [
//                'CLASS' => 'helperTest',
//                'CLONE' => '${CLASS}',
//                'FILTER_LOWER' => '${CLASS|lower}',
//                'FILTER_FIRST_UPPER' => '${CLASS|firstUpper}',
//                'DOUBLE_FILTER' => '${CLASS|lower|firstUpper}',
//                'FILTER_ORDER' => '${CLASS|firstUpper|lower}',
//            ], []
//        );
//
//        Assert::equal('Foo', $schema->translate('Foo'));
//        Assert::equal('helperTest', $schema->translate('${CLASS}'));
//        Assert::equal('helperTest', $schema->translate('${CLONE}'));
//        Assert::equal('helpertest', $schema->translate('${FILTER_LOWER}'));
//        Assert::equal('HelperTest', $schema->translate('${FILTER_FIRST_UPPER}'));
//        Assert::equal('Helpertest', $schema->translate('${DOUBLE_FILTER}'));
//        Assert::equal('helpertest', $schema->translate('${FILTER_ORDER}'));
//    }
//

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

}

\run(new SchemaTranslationTest());
