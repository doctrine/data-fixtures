Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

# Upgrade to 2.3

This release raises the minimum requirements and removes the compatibility shims
for the dropped versions. You now need at least PHP 8.4 and doctrine/persistence 4.0.

Depending on which object manager you load fixtures for, you also need at least:

* DBAL 4.5 and ORM 3.7 — support for DBAL 3 and ORM 2 has been dropped.
* MongoDB ODM 2.17 — support for MongoDB ODM 1 has been dropped.
* PHPCR ODM 3.0 — support for PHPCR ODM 1 and 2 has been dropped.

# Upgrade to 2.0

You need PHP 8.1 or newer to use this library.

## BC BREAK: Add native types declarations to all the methods

All types defined in phpdoc annotations are now defined natively,
they must be added to your code if you extend the classes or implement the interfaces.

## Loggers have to implement the PSR-3 contracts

* Passing a callable to `AbstractExecutor::setLogger()` is not possible anymore, pass a PSR-3 logger instead.
* The method `AbstractExecutor::log()` has been removed without replacement.

## Specifying the class is mandatory when loading references

For the following method, the `class` param is now mandatory:
- `AbstractFixture::getReference`
- `AbstractFixture::hasReference`
- `ReferenceRepository::setReferenceIdentity`
- `ReferenceRepository::hasIdentity`
- `ReferenceRepository::getReference`
- `ReferenceRepository::setReference`

The following method was removed:
- `ReferenceRepository::getReferences`

The following classes are now final, each of them has an interface you can implement:
- `Doctrine\Common\DataFixtures\Executor\MongoDBExecutor`
- `Doctrine\Common\DataFixtures\Executor\ORMExecutor`
- `Doctrine\Common\DataFixtures\Executor\PHPCSExecutor`
- `Doctrine\Common\DataFixtures\Purger\MongoDBPurger`
- `Doctrine\Common\DataFixtures\Purger\ORMPurger`
- `Doctrine\Common\DataFixtures\Purger\PHPCSPurger`

# Upgrade to 1.8

## Deprecated closure loggers in favor of PSR-3

* Passing a callable to `AbstractExecutor::setLogger()` is deprecated, pass a PSR-3 logger instead.
* The method `AbstractExecutor::log()` is deprecated without replacement.

## Finalized classes

Executor and Purger classes are final, they cannot be extended.
`AbstractExecutor` is internal. It cannot be extended or used as typehint.

# Upgrade to 1.6

## BC BREAK: `CircularReferenceException` no longer extends `Doctrine\Common\CommonException`

We don't think anyone catches this exception in a `catch (CommonException)` statement.

## `doctrine/data-fixtures` no longer requires `doctrine/common`

If you rely on types from `doctrine/common`, you should require that package, regardless of whether other packages require it.

# Between v1.0.0-ALPHA1 and v1.0.0-ALPHA2

The FixtureInterface was changed from

    interface FixtureInterface
    {
        load($manager);
    }

to

    use Doctrine\Common\Persistence\ObjectManager;

    interface FixtureInterface
    {
        load(ObjectManager $manager);
    }
