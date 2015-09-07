Joseki/FileTemplate
===================

[![Build Status](https://travis-ci.org/Joseki/FileTemplate.svg?branch=master)](https://travis-ci.org/Joseki/FileTemplate)
[![Latest Stable Version](https://poser.pugx.org/joseki/file-template/v/stable)](https://packagist.org/packages/joseki/file-template)

Requirements
------------

Joseki/FileTemplate requires PHP 5.4 or higher.

- [Nette Framework](https://github.com/nette/nette)
- [Symfony Console](https://github.com/symfony/Console)


Installation
------------

The best way to install Joseki/FileTemplate is using  [Composer](http://getcomposer.org/):

```sh
$ composer require joseki/file-template
```

Register compiler extension in your `config.neon`:

```yml
extensions:
  FileTemplate: Joseki\FileTemplate\DI\FileTemplateExtension
```

Example
-------

Add the following to your `config.neon`:

```yml
extensions:
  FileTemplate: Joseki\FileTemplate\DI\FileTemplateExtension

FileTemplate:
  # root dir for new files
  rootDir: '%appDir%' # [OPTIONAL], %appDir% is default

  # list of file templates groups
  commands:
    example1: # group name used in command line
      variables: ['CONTROL', 'NAMESPACE']
      templates:
        CONTROL_FILE: '%appDir%/templates/control.txt'
        FACTORY_FILE: '%appDir%/templates/factory.txt'
        TEMPLATE_FILE: '%appDir%/templates/template.txt'
      defaults: # [OPTIONAL] default values for variables
        CONTROL_FILE: '${CONTROL}.php'
        FACTORY_FILE: '${CONTROL}Factory.php'
        TEMPLATE_FILE: template.latte
```

Running a console command
-------------------------

```sh
app/console joseki:file-template COMMAND [--dir=RELATIVE_DIRECTORY]
```

for example:

```sh
app/console joseki:file-template example1
```

Suppose that variables `${CONTROL} = Hello` and `${NAMESPACE} = Foo`, then the previous command will create these files:

- %appDir%/Foo/Hello.php
- %appDir%/Foo/HelloFactory.php
- %appDir%/Foo/template.latte

and when a directory is specified:

```sh
app/console joseki:file-template example1 --dir=MyApplication/Auth
```

...these files will be created:

- %appDir%/MyApplication/Auth/Hello.php
- %appDir%/MyApplication/Auth/HelloFactory.php
- %appDir%/MyApplication/Auth/template.latte

NOTE: you will be prompted to define your file template variables

NOTE: this extension should be compatible with [Joseki/Console](https://github.com/Joseki/Console) and [Kdyby/Console](https://github.com/Kdyby/Console).

