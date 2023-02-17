Fixture ordering
================

There are two interfaces you can implement in your fixtures to control
in which order they are going to be loaded.

* By implementing ``OrderedFixtureInterface``, you will be able to
  manually specify a priority for each fixture.
* By implementing ``DependencyFixtureInterface``, you will be able to
  declare which class must be loaded after which classes (note the
  plural), and let the package figure out the order for you.

.. note::
    You may implement an interface in a fixture, and another interface
    in another fixture, and even no interface (besides
    ``FixtureInterface``) in a third one. Implementing both in the same
    fixture is an error.

Option 1: Controlling the order manually
----------------------------------------

.. code-block:: php

    <?php

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use Doctrine\Persistence\ObjectManager;

    final class MyFixture extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load(ObjectManager $manager): void
        {
            // …
        }

        public function getOrder(): int
        {
            return 10; // smaller means sooner
        }
    }

.. note::
    While extending ``AbstractFixture`` is not required, it is likely
    you are going to need it since people usually need fixtures to be
    loading in a specific order because of references from one fixture
    to the other.

Option 2: Declaring dependencies
--------------------------------

If you have many models, and a project that evolves, there may be
several correct orders. Using ``OrderedFixtureInterface`` may become
impractical in case you need to insert a new fixture in a position where
there is no gap in the order. Instead of always renumbering the
fixtures, or being careful to leave big gaps, you can declare that your
fixture must be loaded after some other fixtures, and let the package
figure out what to do.

.. code-block:: php

    <?php
    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\DependentFixtureInterface;
    use Doctrine\Persistence\ObjectManager;

    class MyFixture extends AbstractFixture implements DependentFixtureInterface
    {
        public function load(ObjectManager $manager): void
        {
        }

        /**
         * @return list<class-string<FixtureInterface>>
         */
        public function getDependencies(): array
        {
            return [MyOtherFixture::class];
        }
    }

    class MyOtherFixture extends AbstractFixture
    {
        public function load(ObjectManager $manager): void
        {}
    }
