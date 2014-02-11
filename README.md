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

Here is a simple example of an hypothetical fixture responsible to create a file.

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
This means implementers of this interface can define other fixtures that they
depend on during import/purge process. The interface `Doctrine\Fixture\Sorter\DependentFixture` 
enforces the implementation of a method called `getDependencyList` which 
requires the return to be an array of fully qualified class names of required 
fixtures in order for the implemented one to fully work.

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
    
    /**
     * Returns a list of fixture classes (fully qualified class names) on which
     * implementing class depends on.
     *
     * @return array<string>
     */
    public function getDependencyList()
    {
        return array(
            'MyDataFixtures\UserData',
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

Ordered fixtures follow a sequential order starting from `1`. The sorter for
this type of ordering is behind the scenes a `SplPriorityQueue` instance, so 
multiple fixtures pointing to same order position will be treated as first 
come, first served (FIFO).

To implement a numeric based priority, you have to consume `Doctrine\Fixture\Sorter\OrderedFixture` 
which forces the method `getOrder` to be implemented.

```php
<?php

namespace MyDataFixtures;

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

?>
```

## Filter related fixtures

Testing an application may become a huge headache when the time needed to 
create testing fixtures increases. It is also valid when the amount of fixtures
to be loaded increases. The ability to filter to only load a subset of your 
entire fixture set is a very good way to reduce load time.

Doctrine data fixtures library implements support for filtering fixtures during
importing and purging time.

### GroupedFixture

Grouped fixtures allows you to create group names for fixtures that need to
participate of importing or purging as a unique block. A given fixture can be
part of multiple groups. You can also import or purge multiple groups as 
detailed in GroupedFilter section of this document.

To add named group support, you have to implement the interface `Doctrine\Fixture\Filter\GroupedFixture` 
which enforces method `getGroupList` to be implemented.

```php
<?php

namespace MyDataFixtures;

use Doctrine\Fixture\Filter\GroupedFixture;

class CountryData implements GroupedFixture
{
    public function getGroupList()
    {
        return array(
            'geo', 
            'geolocation',
        );
    }

    public function import()
    {
        // Do your import tasks for CountryData
    }

    public function purge()
    {
        // Do your purge tasks for CountryData
    }
}

?>
```

## Persistence related fixtures

### ConnectionRegistryFixture

Most of the times, fixtures need to communicate with a RDBMS storage, more 
specifically, a Doctrine DBAL connection. These connections are referenced 
through a Registry available in `Doctrine\Common\Persistence\ConnectionRegistry`.

A `Doctrine\Fixture\Persistence\ConnectionRegistryFixture` implementer
proactively receives a `ConnectionRegistry` when used in conjunction with a 
`Doctrine\Fixture\Persistence\ConnectionRegistryEventSubscriber`.

Implementing a `ConnectionRegistryFixture` requires the interface contract
to be implemented.

```php
<?php

namespace MyDataFixtures;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Fixture\Persistence\ConnectionRegistryFixture;

class CompanyData implements ConnectionRegistryFixture
{
    /**
     * @var \Doctrine\Common\Persistence\ConnectionRegistry
     */
    private $connectionRegistry;

    public function setConnectionRegistry(ConnectionRegistry $registry)
    {
        $this->connectionRegistry = $registry;
    }

    public function import()
    {
        // Do your import tasks for CompanyData
    }
    
    public function purge()
    {
        // Do your purge tasks for CompanyData
    }
}

?>
```

### ManagerRegistryFixture

Fixtures that relies on Doctrine ORM have the ability to inject the `ManagerRegistry`
through `Doctrine\Fixture\Persistence\ManagerRegistryEventSubscriber` event 
subscriber. To take advantage of this event subscriber, fixture that is 
interested to have this injection needs to implement the `Doctrine\Fixture\Persistence\ManagerRegistryFixture`.

```php
<?php

namespace MyDataFixtures;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Fixture\Persistence\ManagerRegistryFixture;

class UserData implements ManagerRegistryFixture
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    public function setManagerRegistry(ManagerRegistry $registry)
    {
        $this->managerRegistry = $registry;
    }

    public function import()
    {
        // Do your import tasks for UserData
    }
    
    public function purge()
    {
        // Do your purge tasks for UserData
    }
}

?>
```

# Handling references

An enterprise application does not consist purely of a single fixture. There 
may be many fixtures that may be interdependent and references another and 
these  references can be a database reference, a cache entry or even a file.
Doctrine data fixtures helps you relating these things together through a 
concept called reference repository.

## Implementing a ReferenceRepositoryFixture

To benefit from the straight access to a `Doctrine\Fixture\Reference\ReferenceRepository`,
it is required to implement the contract defined by `Doctrine\Fixture\Reference\ReferenceRepositoryFixture` 
interface. Example:

```php
<?php

namespace MyDataFixtures;

use Doctrine\Fixture\Sorter\DependentFixture;
use Doctrine\Fixture\Reference\ReferenceRepository;
use Doctrine\Fixture\Reference\ReferenceRepositoryFixture;

class ContributorData implements ReferenceRepositoryFixture
{
    /**
     * @var \Doctrine\Fixture\Reference\ReferenceRepository
     */
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function import()
    {
        $contributor = new User();
        $contributor->setName('Guilherme Blanco');

        $this->referenceRepository->add('gblanco', $contributor);
    }
    
    public function purge()
    {
        $this->referenceRepository->remove('gblanco');
    }
}

/**
 * NOTE: Important to note that ReferenceRepositories should be carefully
 * thought. In this simple example we depend on another Fixture to properly
 * load project data (contributor data). This means that not only we implement 
 * the ReferenceRepositoryFixture, but we also implement the DependentFixture.
 */
class ProjectData implements ReferenceRepositoryFixture, DependentFixture
{
    /**
     * @var \Doctrine\Fixture\Reference\ReferenceRepository
     */
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function getDependencyList()
    {
        return array(
            __NAMESPACE__ . '\ContributorData',
        );
    }

    public function import()
    {
        $project = new Project();
        $project->setName('Doctrine Data Fixtures');
        $project->addContributor($this->referenceRepository->get('gblanco'));

        $this->referenceRepository->add('data-fixtures', $project);
    }
    
    public function purge()
    {
        $this->referenceRepository->remove('data-fixtures');
    }
}

?>
```


## DoctrineCacheReferenceRepository

Now that we know from consumer's perspective how to benefit from a Reference
Repository, it is now time to understand how to enable support in Executor.

By default, this library allows you to contains proxy references using a
Doctrine cache provider, but you have the ability to implement your own if
needed. To create and assign a reference repository using a Doctrine cache
provider is done through this piece of code:

```php
<?php

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Reference\DoctrineCacheReferenceRepository;
use Doctrine\Fixture\Reference\ReferenceRepositoryEventSubscriber;

$configuration = new Configuration();
$eventManager  = $configuration->getEventManager();

$eventManager->addEventSubscriber(
    new ReferenceRepositoryEventSubscriber(
        new DoctrineCacheReferenceRepository(
            new ApcCache()
        )
    )
);

// Create your executor, loader, filter here ...

?>
```

## Custom reference repository

Doctrine data fixtures library already comes with Doctrine cache provider 
support natively, but it may not be enough on very specific situations.
In this circumstance, you are required to implement your own custom reference
repository, and we are here to help you on this task.

Reference repository support has an interface that defines the contract for any
possible specialization: `Doctrine\Fixture\Reference\ReferenceRepository`. Its
API is very straight forward and simple, so simple that our showcase will be a
stripped version (comments removed) of Doctrine cache provider implementation.

```php
<?php

namespace MyReferenceRepositoryImpl;

use Doctrine\Common\Cache\Cache;
use Doctrine\Fixture\Reference\ReferenceRepository;

class DoctrineCacheReferenceRepository implements ReferenceRepository
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @api \Doctrine\Fixture\Reference\ReferenceRepository
     */
    public function add($key, $value)
    {
        $this->cache->save($key, $value);
    }

    /**
     * @api \Doctrine\Fixture\Reference\ReferenceRepository
     */
    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    /**
     * @api \Doctrine\Fixture\Reference\ReferenceRepository
     */
    public function has($key)
    {
        return $this->cache->contains($key);
    }

    /**
     * @api \Doctrine\Fixture\Reference\ReferenceRepository
     */
    public function remove($key)
    {
        $this->cache->delete($key);
    }
}

?>
```

Because the `ReferenceRepository` is a well defined contract, the related event
subscriber `Doctrine\Fixture\Reference\ReferenceRepositoryEventSubscriber` 
relies on an implementation of `Doctrine\Fixture\Reference\ReferenceRepository`
and it will normally operate without any problem.

# Creating loaders

A loader is responsible to elect which files are going to be loaded as part of
purging or importing execution.
Loader API provides you a single method to load and make fixtures available
immediately, called `load()`.
In previous examples simple/single loaders were used to demonstrate how to
consume Data Fixtures library. By default, this library comes with many other
types of loaders.

## ChainLoader

Chain loader is used when multiple combinations of loaders are required as part
of any execution. Chain would group these loaders together returning to you a
unique combination of Fixtures after all of them are instantiated correctly.
Here is a simple example of a ChainLoader being used together with two class
loaders.

```php
<?php

use Doctrine\Fixture\Loader\ChainLoader;
use Doctrine\Fixture\Loader\ClassLoader;

$classLoaderA = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA'));
$classLoaderB = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB'));

$loader = new ChainLoader(array(
    $classLoaderA,
    $classLoaderB,
));

// Returns: array(Doctrine\Test\Mock\Unassigned\FixtureA, Doctrine\Test\Mock\Unassigned\FixtureB)
$fixtureList = $loader->load();

?>
```

Also, ChainLoader provides a nice API to add/remove loaders this its list:

```php
<?php

use Doctrine\Fixture\Loader\ChainLoader;
use Doctrine\Fixture\Loader\ClassLoader;

$classLoaderA = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA'));
$classLoaderB = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB'));

$loader = new ChainLoader(array(
    $classLoaderA,
    $classLoaderB,
));

$loaderList = $loader->getLoaderList(); // returns array($classLoaderA, $classLoaderB)

$loader->removeLoader($classLoaderA);

$loaderList = $loader->getLoaderList(); // returns array($classLoaderB)

$loader->addLoader($classLoaderA);

$loaderList = $loader->getLoaderList(); // returns array($classLoaderB, $classLoaderA)

?>
```

## ClassLoader

Previously we used ClassLoader in examples, but this loader supports a single 
specific loader for a given class relying on its autoloader to be properly
imported/executed. ClassLoader accepts a list of classes in constructor for 
loading.

For sanity, here is a simplistic example of a ClassLoader being created:

```php
<?php

use Doctrine\Fixture\Loader\ClassLoader;

$classLoaderA = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA'));

?>
```

## DirectoryLoader

It is normal that a set of fixtures sit in a directory. Implementing multiple
ClassLoaders or similar would be a huge effort, so natively we offer support to
load fixtures by a given directory.

NOTE: For recursive directory loading, please refer to RecursiveDirectoryLoader 
section.

Example:

```php
<?php

use Doctrine\Fixture\Loader\DirectoryLoader;

$directoryLoader = new DirectoryLoader('/path/to/fixtures/directory');

$fixtureList = $directoryLoader->load();

?>
```

## GlobLoader

Similar to DirectoryLoader, GlobLoader loads a set of files based on a glob 
expression. For more information about glob, ([read the PHP manual](http://php.net/GlobIterator)).

```php
<?php

use Doctrine\Fixture\Loader\GlobLoader;

$globLoader = new GlobLoader('/some/directory/*Fixture.php');

?>
```

## RecursiveDirectoryLoader

DirectoryLoader is only able to load files on a single depth. 
RecursiveDirectoryLoader is a specialized version of DirectoryLoader, 
traversing a directory tree starting from provided path and loading all files 
it finds. This is very useful when used together with namespaces and PSR-0 or 
PSR-4.

```php
<?php

use Doctrine\Fixture\Loader\RecursiveDirectoryLoader;

$directoryLoader = new RecursiveDirectoryLoader('/path/to/fixtures/directory');

$fixtureList = $directoryLoader->load();

?>
```

## Custom loaders

Doctrine data fixtures provides by default a handful of loaders natively.
Depending on your needs, it may not be enough. By implementing the `Loader`
interface, you are able to plugin your custom loader implementation and still
benefit from the data fixtures library easily.

In our example here, let's assume we need to traverse recursively a directory
and find all files that ends with `Fixture.php`. You may understand this 
example as a RecursiveDirectoryLoader + GlobLoader like implementation, but
instead of considering a glob, we will accept a regular expression. 
We should also consider that it must be a valid Fixture to be loaded, and we 
also want to validate against this criteria.
To achieve our goal, we will accept 2 arguments in our loader constructor: 
directory and pattern. 

Here is our loader's code:

```php
<?php

use Doctrine\Fixture\Loader\Loader;

class RecursiveFilterableDirectoryLoader implements Loader
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * Constructor.
     *
     * @throws \InvalidArgumentException
     *
     * @param string $directory
     * @param string $pattern
     */
    public function __construct($directory, $pattern)
    {
        if ( ! is_dir($directory)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $directory));
        }

        $this->directory = $directory;
        $this->pattern   = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $traversable       = $this->loadFromFilesystem();
        $fileList          = $this->loadFromTraversable($traversable);
        $declaredClassList = get_declared_classes();
        $fixtureList       = array();

        foreach ($declaredClassList as $class) {
            $reflectionClass = new \ReflectionClass($class);

            // Check if class was declared during this loader
            if ( ! in_array($reflectionClass->getFileName(), $fileList)) {
                continue;
            }

            // Check if class is transient
            if ($this->isTransient($reflectionClass)) {
                continue;
            }

            $fixtureList[] = new $class();
        }

        return $fixtureList;
    }

    /**
     * Loads fixture files from filesystem matching a regex pattern.
     *
     * @return \FilesystemIterator
     */
    protected function loadFromFilesystem()
    {
        $directoryIterator = new \RecursiveDirectoryIterator($this->directory);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::LEAVES_ONLY);

        return new RegexIterator($recursiveIterator, $this->pattern, RegexIterator::GET_MATCH);
    }

    /**
     * Loads the file list using a given traversable.
     *
     * @param \Traversable $traversable
     *
     * @return array
     */
    private function loadFromTraversable(\Traversable $traversable)
    {
        $fileList = array();

        foreach ($traversable as $fileInfo) {
            if ($fileInfo->getBasename('.php') === $fileInfo->getBasename()) {
                continue;
            }

            $fileRealPath = $fileInfo->getRealPath();

            require_once $fileRealPath;

            $fileList[] = $fileRealPath;
        }

        return $fileList;
    }

    /**
     * Checks if class is transient.
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @return boolean
     */
    private function isTransient(\ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->isAbstract()) {
            return true;
        }

        return ( ! $reflectionClass->implementsInterface('Doctrine\Fixture\Fixture'));
    }
}

?>
```

Now, to consume our newly created code, we should do:

```php
<?php

$myCustomLoader = new RecursiveFilterableDirectoryLoader(
    '/path/to/fixtures/directory', 
    '/(.*)Fixture\.php$/'
);

?>
```

# Creating filters

Doctrine data fixtures 1.0 had a limitation on selectively importing/purging 
fixtures. Starting from version 2.0, you are now able to filter the list of
classes to be executed. This is done by using a concept called filter.

Because this is a dangerous territory, Doctrine data fixtures library is only
able to bring basic support for filtering. It is up to you provide how you
want to filter. To simplify most common cases, this library offers a default
implementation that should be sufficient in normal cases, using a name based
filter, and also a way to chain multiple filters together.

## ChainFilter

ChainFilter offers the ability to add multiple filters at the same time for 
execution.

Example:

```php
<?php

use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Filter\GroupedFilter;

use Doctrine\Fixture\Loader\ClassLoader;

$groupedFilterA = new GroupedFilter(array('A'));
$groupedFilterB = new GroupedFilter(array('B'));

$chainFilter = new ChainFilter(array(
    $groupedFilterA,
    $groupedFilterB,
));

$classLoaderA = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA'));
$fixtureList  = $classLoaderA->load();

$chainFilter->accept($fixtureList[0]); // Returns boolean


$filterList = $chainFilter->getFilterList(); // returns array($groupedFilterA, $groupedFilterB)

$chainFilter->removeLoader($groupedFilterA);

$filterList = $chainFilter->getFilterList(); // returns array($groupedFilterB)

$chainFilter->addLoader($groupedFilterA);

$filterList = $chainFilter->getFilterList(); // returns array($groupedFilterB, $groupedFilterA)

?>
```

## GroupedFilter

In previous example, we demonstrated how to create a GroupedFilter.
This filter defines a restriction to load specific fixtures that matches a
contract implementation `GroupedFixture` and also a key matching.

Let's suppose we have 2 fixtures:

```php
<?php

use Doctrine\Fixture\Fixture;
use Doctrine\Fixture\Filter\GroupedFixture;

class FixtureA implements Fixture, GroupedFixture
{
    public function getGroupList()
    {
        return array('A', 'other_keyword');
    }

    public function import() { /* ... */}

    public function purge() { /* ... */ }
}

class FixtureB implements Fixture
{
    public function import() { /* ... */}

    public function purge() { /* ... */ }
}

?>
```

Let's assume we have this initialization piece:

```php
<?php

use Doctrine\Fixture\Loader\ChainLoader;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Executor;

$classLoader = new ClassLoader(array(
    'FixtureA',
    'FixtureB',
));

$executor = new Executor(new Configuration());

?>
```

GroupedFilter requires a fixture to implement GroupedFixture to check for
matching filter, unless a configuration tells the opposite. Let's consider
two possibilities:

```php
<?php

use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Filter\GroupedFilter;

$filter = new GroupedFilter(array('A'));

$executor->execute($classLoader, $filter, Executor::IMPORT); // would load FixtureA and FixtureB

?>
```

Assuming only implementors should be considered, GroupedFilter accepts a second
argument called `onlyImplementors`. This means that fixtures that does not 
implement GroupedFixture interface would then be dicarded. Example:

```php
<?php

use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Filter\GroupedFilter;

$filter = new GroupedFilter(array('A'), true);

$executor->execute($classLoader, $filter, Executor::IMPORT); // would load FixtureA only

?>
```

## Custom filters

It is easily possible to create your own filter. All it is required is to
implement the Filter API. Here it is:

```php
<?php

interface Filter
{
    /**
     * Checks whether the fixture is acceptable.
     *
     * @param \Doctrine\Fixture\Fixture $fixture
     *
     * @return boolean
     */
    function accept(Fixture $fixture);
}

?>
```

By implementing your own filter support, it easily supports fine grained 
solutions for your personal projects. A simple example is to create
personalized interfaces mimic-ing same support defined in GroupedFilter.

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
