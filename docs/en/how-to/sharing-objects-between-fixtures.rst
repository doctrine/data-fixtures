Sharing objects between fixtures
================================

Your models are likely to have relationships with each other. Because of
that, it can be interesting to create and persist an object in a
fixture, and then reference it in another fixture.

Assuming you have a ``User`` and a ``Role`` model, here is an example
showing how to use the ``AbstractFixture`` class to do that.

.. note::

   ``AbstractFixture`` implements ``FixtureInterface``, which means the
   requirement mentioned in the :doc:`previous how-to guide
   <loading-fixtures>` is satisfied if you extend ``AbstractFixture``.

.. code-block:: php

    <?php
    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Persistence\ObjectManager;

    class UserRoleDataLoader extends AbstractFixture
    {
        public function load(ObjectManager $manager): void
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

And the ``User`` data loading fixture:

.. code-block:: php

    <?php

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Persistence\ObjectManager;

    class UserDataLoader extends AbstractFixture
    {
        public function load(ObjectManager $manager): void
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');
            $user->setRole(
                $this->getReference('admin-role', Role::class) // load the stored reference
            );

            $manager->persist($user);
            $manager->flush();

            // store reference of admin-user for other Fixtures
            $this->addReference('admin-user', $user);
        }
    }

Note that because that last fixture depends on the first one, the order
in which fixtures are loading becomes important. You can learn more
about how to manage loading order in :doc:`the dedicated guide
<fixture-ordering>`.
