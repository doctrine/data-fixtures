Note about upgrading: Doctrine uses static and runtime mechanisms to raise
awareness about deprecated code.

- Use of `@deprecated` docblock that is detected by IDEs (like PHPStorm) or
  Static Analysis tools (like Psalm, phpstan)
- Use of our low-overhead runtime deprecation API, details:
  https://github.com/doctrine/deprecations/

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
