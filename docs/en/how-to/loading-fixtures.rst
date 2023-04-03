Loading fixtures
================

Let us assume you have an existing project with a ``User`` model.
To create a fixture for that model, there are three steps:

#. create a fixture class.
#. load that fixture with a loader.
#. execute the fixture with an executor.

Creating a fixture class
------------------------

Fixture classes have two requirements:

* They must implement ``Doctrine\Common\DataFixtures\FixtureInterface``.
* If they have a constructor, that constructor should be invokable
  without arguments.

.. code-block:: php

    <?php

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\FixtureInterface;
    use Doctrine\Persistence\ObjectManager;

    class UserDataLoader implements FixtureInterface
    {
        public function load(ObjectManager $manager): void
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');

            $manager->persist($user);
            $manager->flush();
        }
    }

.. note::

    ``FixtureInterface`` is in the ``Common`` namespace, because it once
    was in the ``doctrine/common`` package, which was split in several
    packages. The namespace was retained for backward compatibility.

Loading fixtures
----------------

To load a fixture, you can call ``Loader::addFixture()``:

.. code-block:: php

    <?php

    use Doctrine\Common\DataFixtures\Loader;
    use MyDataFixtures\UserDataLoader;

    $loader = new Loader();
    $loader->addFixture(new UserDataLoader());

It is also possible to load a fixture by providing its path:

.. code-block:: php

    <?php
    $loader->loadFromFile('/path/to/MyDataFixtures/MyFixture1.php');

If you have many fixtures, this can get old pretty fast, and you might
want to load a whole directory of fixtures instead of making one call
per fixture.

.. code-block:: php

    <?php
    $loader->loadFromDirectory('/path/to/MyDataFixtures');

You can get the added fixtures using the ``getFixtures()`` method:

.. code-block:: php

    <?php
    $fixtures = $loader->getFixtures();

Executing fixtures
------------------

To load the fixtures in your data store, you need to execute them. This
is when you need to pick different classes depending on the type of
store you are using. For example, if you are using ORM, you should
do the following:

.. code-block:: php

    <?php
    use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
    use Doctrine\Common\DataFixtures\Purger\ORMPurger;

    $executor = new ORMExecutor($entityManager, new ORMPurger());
    $executor->execute($loader->getFixtures());

.. note::

    Each executor class provided by this package comes with a purger
    class that will be used to empty your database unless you explicitly
    disable it.

If you want to append the fixtures instead of purging before loading
then pass ``append: true`` to the ``execute()`` method:

.. code-block:: php

    <?php
    $executor->execute($loader->getFixtures(), append: true);

By default the ``ORMExecutor`` will wrap the purge and the load of fixtures
in a single transaction, which is the recommended way, but in some cases (for
example if loading your fixtures is too slow and causes timeouts) you may
want to wrap the purge and the load of every fixture in its own transaction.
To do so, you can use ``MultipleTransactionORMExecutor``.

.. code-block:: php

    <?php
    $executor = new MultipleTransactionORMExecutor($entityManager, new ORMPurger());
