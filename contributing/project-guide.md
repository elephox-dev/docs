---
layout: default
title: Project Guide
parent: Contributing
---

# Project Guide
{: .no_toc}

- TOC
{:toc}

---

## Directory Structure

```
philly-framework/base/
 ├── modules/
 │    ├── Collection/
 │    │    ├── src/
 │    │    │    ├── Contracts/
 │    │    │    │    ├── GenericList.php
 │    │    │    │    └── ReadonlyList.php
 │    │    │    └── ArrayList.php
 │    │    ├── test/
 │    │    │    └── ArrayListTest.php
 │    │    └── composer.json
 │    ├── DI/...
 │    ├── Http/...
 │    ├── Support/...
 │    └── Text/...
 ├── composer.json
 ├── infection.json
 ├── phpunit.xml.dist
 └── psalm.xml
```

The `base` repository contains all the source code for the Philly framework. It is organized in modules, which are 
sub-directories of the repository root. Each module contains a `src` and a `test` directory, which contain the source 
code and the unit tests for it, respectively. Each module also contains a `composer.json`, which allows each module to
be published on packagist.org independently.

## Development Environment Setup

To set up your local environment, you need to have the following:

- A working version of [PHP 8.1](https://qa.php.net/)
- A text editor (preferably something like [PhpStorm](https://www.jetbrains.com/phpstorm/) or [VS Code](https://code.visualstudio.com/))
- A working [Composer](https://getcomposer.org/) installation or `composer.phar` in your `PATH`
- A configured [Git](https://git-scm.com/) client

```bash
# clone the sources
$ git clone git@github.com:philly-framework/base.git # or git clone https://github.com/philly-framework/base.git
$ cd base

# install dependencies
$ composer install # php composer.phar install
```

And you're ready to go!

## Tooling

Philly uses multiple tools to analyze and test the code.

### .editorconfig

This project uses an `.editorconfig` file to establish a common file configurations across common IDEs and editors.
Namely, these are:

- Charset: UTF-8
- End of line: LF
- Insert final newline: true
- Trim trailing whitespace: true

For `.php`-files:
- Indentation: tabs

For `.json`-files:
- Indentation: 4 spaces

No tool enforces these settings, so you can code the way you want to. Please be aware that you might have to adapt your
code when opening a pull request.

### PHPUnit & Infection

To execute tests, Philly uses [PHPUnit](https://phpunit.de/).

To run PHPUnit locally, execute

```bash
$ vendor/bin/phpunit
```

or with a specific module to test:

```bash
$ vendor/bin/phpunit --testsuite Collection
```

Additionally, Philly uses [Infection](https://infection.github.io/) to analyze how effective the tests really are.
For Infection, execute

```bash
$ vendor/bin/infection --show-mutations
```

Infection generates a metric called "Covered Code MSI". This metric needs to be above 90%, meaning at least 90% of the
code covered by tests should be resistant to mutations. Mutations are small modifications to the tested code, which
should cause tests to fail. An escaped mutant is a change to the code which was not detected by a failed test.

### Psalm

The codebase is statically analyzed by [Psalm](https://psalm.dev).
Psalm uses docblocks and type-hints to infer the types of variables and return types of functions.
This way, it can check for possible errors before they even happen.

To execute Psalm locally, run

```bash
$ vendor/bin/psalm
```

Ideally, psalm should report no errors. If it does, please try to refactor the code to fix the errors.

### Code coverage

Once code is pushed to the `main` branch, the code coverage is automatically updated by PHPUnit.
You can view the coverage report at [philly.ricardoboss.de/coverage/](https://philly.ricardoboss.de/coverage/).

You can also view the coverage report locally by executing

```bash
$ vendor/bin/phpunit --coverage-html=tmp/coverage/html # you can also specify a testsuite here to only show coverage for that module
```

and then navigating to the `index.html` within the `tmp/coverage/html` directory.

## What next?

 - [Check the to-do list](https://github.com/philly-framework/base/blob/main/README.md)
 - [Leave a star](https://github.com/philly-framework/base)
