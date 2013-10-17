# Doctrine Fixtures library

[![Build Status](https://travis-ci.org/doctrine/data-fixtures.png?branch=2.0)](https://travis-ci.org/doctrine/data-fixtures)

This library aims to provide a simple way to manage and execute the loading 
and/or purging of data fixtures for the Doctrine ORM or ODM. 
It is build around events, which provides a powerful extension point to add any 
new extra support for 3rd party libraries without touching the core functionality.

# Installing

Doctrine Fixtures library is available on Packagist ([doctrine/data-fixtures](http://packagist.org/packages/doctrine/data-fixtures))
and as such installable via [Composer](http://getcomposer.org/).

```bash
php composer.phar require doctrine/data-fixtures '~2.0'
```

If you do not use Composer, you can grab the code from GitHub, and use any 
PSR-0 compatible autoloader (e.g. the [Symfony2 ClassLoader component](https://github.com/symfony/ClassLoader)) 
to load Fixture classes.

# Basic usage

## Creating your first fixture

Writing a data fixtures consists in two steps to be implemented:

- Importing data
- Purging data

To enforce that both of these methods are properly implemented, Doctrine 
Fixtures library provides a contract (interface) that you can follow: `Doctrine\Fixture\Fixture`.
Here is a simple example of an hypotetical fixture responsible to create a file.

```php
<?php
namespace MyDataFixtures;

use Doctrine\Fixture\Fixture;

class FileFixture implements Fixture
{
    const FILENAME = '/tmp/file.ext';

	/**
	 * {@inheritdoc}
	 */
	public function import()
	{
        file_put_contents(self::FILENAME, '');
	}

	/**
	 * {@inheritdoc}
	 */
	public function purge()
	{
        @unlink(self::FILENAME);
	}
}

?>
```

## Creating a simple fixture executor

In order for your fixtures to be properly loaded, it is required that all 
desired fixtures are included into a fixture executor. An executor is a 
piece of functionality responsible to execute fixtures according to desired 
needs. As an example, you may want to purge and import a specific fixture or 
even purely purge a set of fixtures.
Here is an example on how to create a fixture executor that imports our first 
fixture.

```php
<?php

use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Loader\ClassLoader;
use Doctrine\Fixture\Filter\ChainFilter;

$configuration = new Configuration();
$executor      = new Executor($configuration);
$classLoader   = new ClassLoader('MyDataFixtures\FileFixture');
$filter        = new ChainFilter();

$executor->execute($classLoader, $filter, Executor::IMPORT);

?>
```

That's it! As soon as your executor code is processed, your `FileFixture` 
should be imported. There are many more that this library does, so feel free 
to go ahead and dig into details now.

# Writing fixtures

We already saw how to implement a fixture, and that is light years away from 
and actual enterprise product, we all agree. But it exposed an important point 
on how to implement a fixture: you are the responsible to implement the 
relevant logic to import and to purge your data.

More powerful applications demand better support on how to control fixtures 
execution order, persistence support and even ability to filter some of them 
depending on the purpose of the load. To handle all that, Doctrine Fixtures 
library created a set of interfaces that helps you supporting all those, 
without bringing too much complexity.

## Order related fixtures

Whenever you want to change the order of execution of fixtures, you have to 
consider using an interface to control order. Bundled we provide two types of 
ordering that can be used out of the box: `dependent` and `ordered`. 

Doctrine Fixtures library is smart enough to attempt to calculate which sorter 
best matches the fixtures required to be loaded and loads the sorter for you. 
By default it is possible to use even these already implemented order fixtures 
together; the sorter used in this situation is called `mixed` and follows a 
simple rule: 

- Load all ordered fixtures
- Load all dependent fixtures, which may or may not include "unassigned" 
fixtures. An "unassigned" fixture is a simple fixture that does not contain any 
information about its planned order

It is also possible implement your own sorter as it will be detailed later in 
this manual.

### DependentFixture

DependentFixture provides the contract for fixtures that are interdependent. 
This means implementors of this interface can define other fixtures that they 
depend on during import/purge process. It enforces the implementation of a 
method called `getDependencyList` which requires the return to be an array of 
fully qualified class names of required fixtures in order for the implemented 
one to fully work.

```php
<?php

namespace MyDataFixtures;

use Doctrine\Fixture\Sorter\DependentFixture;

class CompanyData implements DependentFixture
{
    public function import()
    {
        // Do your import tasks for CompanyData
    }
    
    public function purge()
    {
        // Do your purge tasks for CompanyData
    }
    
    public function getDependencyList()
    {
        return array(
            'MyDataFixtures\UserData' // You must use the full qualified name
        );
    }
} 

class UserData implements DependentFixture
{
	public function import()
    {
        // Do your import tasks for UserData
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData
    }
    
    public function getDependencyList()
    {
    	// Example of no dependency. This would be the same as having this 
    	// class purely implement Doctrine\Fixture\Fixture.
        return array();
    }
}

?>
```

### OrderedFixture

TBD

## Filter related fixtures

TBD

### GroupedFixture

TBD

## Persistence related fixtures

### ConnectionRegistryFixture

TBD

### ManagerRegistryFixture

TBD

# Handling references

## DoctrineCacheReferenceRepository

TBD

## Custom reference repository

TBD

# Creating loaders

## ChainLoader

TBD

## ClassLoader

TBD

## DirectoryLoader

TBD

## GlobLoader

TBD

## RecursiveDirectoryLoader

TBD

## Custom loaders

TBD

# Creating filters

## ChainFilter

TBD

## GroupedFilter

TBD

## Custom filters

TBD

# Mastering event system

## Executor events

### Bulk Import

TBD

### Bulk Purge

TBD

## BulkExecutor events

### Import

TBD

### Purge

TBD

# Creating persisters

## Existing persisters

TBD

## Custom persisters

TBD

# Creating sorters

## Existing sorters

TBD

## Custom sorter

TBD

# License

Doctrine Fixtures library is licensed under the MIT License - see the `LICENSE` file for details

# Internals

## Running the tests:

To setup and run tests follow these steps:

- Go to the root directory of data-fixtures
- Run: **composer install --dev**
- Copy the phpunit config **cp phpunit.xml.dist phpunit.xml**
- Run: **./vendor/bin/phpunit**
