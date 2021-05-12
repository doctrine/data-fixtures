# Doctrine Data Fixtures Extension

[![Build Status](https://github.com/doctrine/data-fixtures/workflows/Continuous%20Integration/badge.svg)](https://github.com/doctrine/data-fixtures/actions)

This extension aims to provide a simple way to manage and execute the loading of data fixtures
for the [Doctrine ORM or ODM](http://www.doctrine-project.org/). You can write fixture classes
by implementing the [`Doctrine\Common\DataFixtures\FixtureInterface`](lib/Doctrine/Common/DataFixtures/FixtureInterface.php) interface:

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserDataLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('jwage');
        $user->setPassword('test');

        $manager->persist($user);
        $manager->flush();
    }
}
```

Now you can begin adding the fixtures to a loader instance:

```php
use Doctrine\Common\DataFixtures\Loader;
use MyDataFixtures\UserDataLoader;

$loader = new Loader();
$loader->addFixture(new UserDataLoader());
```

You can load a set of fixtures from a directory as well:

```php
$loader->loadFromDirectory('/path/to/MyDataFixtures');
```

Or you can load a set of fixtures from a file:

```php
$loader->loadFromFile('/path/to/MyDataFixtures/MyFixture1.php');
```

You can get the added fixtures using the getFixtures() method:

```php
$fixtures = $loader->getFixtures();
```

Now you can easily execute the fixtures:

```php
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

$purger = new ORMPurger();
$executor = new ORMExecutor($em, $purger);
$executor->execute($loader->getFixtures());
```

If you want to append the fixtures instead of purging before loading then pass true
to the 2nd argument of execute:

```php
$executor->execute($loader->getFixtures(), true);
```

## Sharing objects between fixtures

In case if fixture objects have relations to other fixtures, it is now possible
to easily add a reference to that object by name and later reference it to form
a relation. Here is an example fixtures for **Role** and **User** relation

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserRoleDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $adminRole = new Role();
        $adminRole->setName('admin');

        $anonymousRole = new Role();
        $anonymousRole->setName('anonymous');

        $manager->persist($adminRole);
        $manager->persist($anonymousRole);
        $manager->flush();

        // store reference to admin role for User relation to Role
        $this->addReference('admin-role', $adminRole);
    }
}
```

And the **User** data loading fixture:

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('jwage');
        $user->setPassword('test');
        $user->setRole(
            $this->getReference('admin-role') // load the stored reference
        );

        $manager->persist($user);
        $manager->flush();

        // store reference of admin-user for other Fixtures
        $this->addReference('admin-user', $user);
    }
}
```

### Random references
You can call a reference randomly. To do this, you will need to 
define a set of references with a common tag. 

For instance:
```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserRoleDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i < 10; $i++) {
            $role = new Role();
            $role->setName('name-'.$i);

            $manager->persist($role);
            
            // store tagged Reference of current role for other Fixtures
            $this->addReference('role-'.$i, $role, 'role');
        }

        $manager->flush();
    }
}
```

Get a random **Role** reference for **User**.

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('jwage');
        $user->setPassword('test');
        $user->setRole(
            $this->getRandomReference('role') // load the stored reference
        );

        $manager->persist($user);
        $manager->flush();
    }
}
```

### Unique references
You can generate unique references that are destroyed after use.
Unique references are always tagged and invalidated only within the 
scope of the assigned tag:

```php
$this->addUniqueReference('ref-a', $refa, 'tag-a');
$this->addUniqueReference('ref-a', $refa, 'tag-b');

$this->getUniqueReference('ref-a', 'tag-a');

// ->getUniqueReference('ref-a', 'tag-a'); // obsolete
// ->getUniqueReference('ref-a', 'tag-b')  // still valid
```

Calling `->getRandomReference` for a tag marking unique references returns 
a unique reference immediately made obsolete. Of course, you must create enough 
unique references for your needs, otherwise an exception will be thrown.

Usage example: You want to generate Actor and Role fixtures. A role cannot 
be assigned to several actors, so your references must be unique.

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class ActorRoleDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i < 10; $i++) {
            $role = new Role();
            $role->setName('name-'.$i);

            $manager->persist($role);
            
            // store unique Reference of current role for other Fixtures
            // you just have to tag it
            $this->addUniqueReference('role-'.$i, $role, 'role');
        }

        $manager->flush();
    }
}
```
Actors fixtures:
```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class ActorDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $actor = new Actor();
        $actor->setUsername('Franck');
        $actor->setRole(
            $this->getUniqueReference('role-1', 'role')
            // or $this->getRandomReference('role')
        );

        $manager->persist($actor);
        $manager->flush();
    }
}
```

## Fixture ordering
**Notice** that the fixture loading order is important! To handle it manually
implement one of the following interfaces:

### OrderedFixtureInterface

Set the order manually:

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder()
    {
        return 10; // number in which order to load fixtures
    }
}
```

### DependentFixtureInterface

Provide an array of fixture class names:

```php
namespace MyDataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyFixture extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array('MyDataFixtures\MyOtherFixture'); // fixture classes fixture is dependent on
    }
}

class MyOtherFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {}
}
```

**Notice** the ordering is relevant to Loader class.

## Running the tests:

Phpunit is included in the dev requirements of this package.

To setup and run tests follow these steps:

- go to the root directory of data-fixtures
- run: **composer install --dev**
- run: **vendor/bin/phpunit**
