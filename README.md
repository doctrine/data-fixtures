# Doctrine Fixtures library

[![Build Status](https://travis-ci.org/doctrine/data-fixtures.png?branch=2.0)](https://travis-ci.org/doctrine/data-fixtures)

## Running the tests:

To setup and run tests follow these steps:

- go to the root directory of data-fixtures
- run: **composer install --dev**
- copy the phpunit config **cp phpunit.xml.dist phpunit.xml**
- run: **./vendor/bin/phpunit**

## Fixture Definition

This extension aims to provide a simple way define your fixtures.

### Basic definition

The ``Doctrine\Fixture\Fixture`` interface defines the basic fixture behaviors (import and purge).
To create a fixture, just implement that interface:

```php
namespace Me\MyProject\Fixtures;

use Doctrine\Fixture\Fixture;

class UserData implements Fixture
{
    public function import()
    {
        // Do your import tasks for UserData
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData
    }
}
```

### Dependent fixtures

You may have some fixtures that depends on others to be executed.
To create a dependent fixture this extension provides you the ``Doctrine\Fixture\Sorter\DependentFixture`` interface:

```php
namespace Me\MyProject\Fixtures;

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
            'Me\MyProject\Fixtures\UserData' // You must use the full qualified name
        );
    }
}
```

### Ordered fixtures

You may want to determine the order of the execution of your fixtures, no problem!
Just implement the ``Doctrine\Fixture\Sorter\OrderedFixture`` interface:

```php
namespace Me\MyProject\Fixtures;

use Doctrine\Fixture\Sorter\OrderedFixture;

class UserData implements OrderedFixture
{
    public function import()
    {
        // Do your import tasks for UserData
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData
    }
    
    public function getOrder()
    {
        return 1;
    }
}

class CompanyData implements OrderedFixture
{
    public function import()
    {
        // Do your import tasks for CompanyData
    }
    
    public function purge()
    {
        // Do your purge tasks for CompanyData
    }
    
    public function getOrder()
    {
        return 2;
    }
}
```

### Fixture groups

This extension provides a way to define the fixture groups, and that information can be used to execute 
just the fixtures of one particular group.
In order to do that your fixture must implement the ``Doctrine\Fixture\Filter\GroupedFixture`` interface:

```php
namespace Me\MyProject\Fixtures;

use Doctrine\Fixture\Filter\GroupedFixture;

class UserData implements GroupedFixture
{
    public function import()
    {
        // Do your import tasks for UserData
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData
    }
    
    public function getGroupList()
    {
        return array('group_1');
    }
}

class CompanyData implements GroupedFixture
{
    public function import()
    {
        // Do your import tasks for CompanyData
    }
    
    public function purge()
    {
        // Do your purge tasks for CompanyData
    }
    
    public function getGroupList()
    {
        return array('group_1', 'group_2');
    }
}
```

### Persistence

To persist your fixture data the extension provides two interfaces:
``Doctrine\Fixture\Persistence\ConnectionRegistryFixture`` and ``Doctrine\Fixture\Persistence\ManagerRegistryFixture``.
Using them your fixtures will receive the ``ConnectionRegistry`` or ``ManagerRegistry`` (by setter), 
so you can use the object to import and purge data from your persistence layer:

```php
namespace Me\MyProject\Fixtures;

use Doctrine\Fixture\Persistence\ManagerRegistryFixture;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserData implements ManagerRegistryFixture
{
    protected $managerRegistry;
    
    public function import()
    {
        // Do your import tasks for UserData (using $this->managerRegistry)
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData (using $this->managerRegistry)
    }
    
    public function setManagerRegistry(ManagerRegistry $registry)
    {
        $this->managerRegistry = $registry;
    }
}
```

## Configuration

Once your fixtures were created, you must set the configuration object.
The configuration object has basically two things: an event manager (which will have all subscribers you want)
and a calculator factory (that will calculate the fixtures execution order).

If your fixtures implements the ``Doctrine\Fixture\Persistence\ManagerRegistryFixture``
or ``Doctrine\Fixture\Persistence\ConnectionRegistryFixture`` interfaces, you should register the
respective event subscribers:

```php
use Doctrine\Common\EventManager;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Persistence\ManagerRegistryEventSubscriber;
use Doctrine\Fixture\Sorter\CalculatorFactory;

$configuration = new Configuration();
$configuration->setEventManager(new EventManager());
$configuration->setCalculatorFactory(new CalculatorFactory());

$configuration->getEventManager()->addEventSubscriber(
    new ManagerRegistryEventSubscriber(/* Your ManagerRegistry should be passed here */);
);
```

## Loading

To load your fixture you must use any class that implements the ``Doctrine\Fixture\Loader\Loader`` interface.
This extension provides these loaders: ``Doctrine\Fixture\Loader\ClassLoader``,
``Doctrine\Fixture\Loader\GlobLoader``, ``Doctrine\Fixture\Loader\DirectoryLoader``
and ``Doctrine\Fixture\Loader\RecursiveDirectoryLoader``.

For example, if you just want to load all fixtures in a directory your loader should be
``Doctrine\Fixture\Loader\DirectoryLoader``:

```php
use Doctrine\Fixture\Loader\DirectoryLoader;

$loader = new DirectoryLoader(__DIR__ . '/Fixtures');
```

You may also combine loaders using the ``Doctrine\Fixture\Loader\ChainLoader``:

```php
use Doctrine\Fixture\Loader\ChainLoader;
use Doctrine\Fixture\Loader\DirectoryLoader;
use Doctrine\Fixture\Loader\GlobLoader;

$loader = new ChainLoader(
    array(
        new DirectoryLoader(__DIR__ . '/Fixtures'),
        new GlobLoader(__DIR__ . '/Entity/*Fixture.php')
    )
);
```

## Filtering

The ``Doctrine\Fixture\Executor`` can filter the fixtures using a ``Doctrine\Fixture\Filter``.
This extension provides a basic filter called ``Doctrine\Fixture\Filter\GroupedFilter``, that filters
the fixtures that belongs to on of the given groups:

```php
use Doctrine\Fixture\Filter\GroupedFilter;

$filter = new GroupedFilter(array('group_1', 'group_2'));
```

You may also combine filters using the ``Doctrine\Fixture\Filter\ChainFilter``:

```php
use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Filter\GroupedFilter;
use Me\MyProject\Fixtures\MyFilter;

$filter = new ChainFilter(
    array(
        new GroupedFilter(array('group_1', 'group_2')),
        new MyFilter() // Your custom filter
    )
);
```

## Executing

And now it's time to gather all things together and execute your import and/or purge tasks.
In order to that you must create a new ``Doctrine\Fixture\Executor`` passing your configuration, and run
the execute method:

```php
use Doctrine\Fixture\Executor;

$flags    = Executor::IMPORT; // The execution flags can be Executor::IMPORT and Executor::PURGE,
                              // if you want to execute purge and import on a single execution you may use
                              // Executor::IMPORT | Executor::PURGE
$executor = new Executor($configuration);
$executor->execute($loader, $filter, $flags);
```
