Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

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
