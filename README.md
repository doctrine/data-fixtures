# Doctrine Data Fixtures Extension

This extension aims to provide a simple way to manage and execute the loading of data fixtures
for the Doctrine ORM or ODM. You can write fixture classes by implementing the
Doctrine\Common\DataFixtures\FixtureInterface interface:

    namespace MyDataFixtures;

    use Doctrine\Common\Persistence\ObjectManager;
    use Doctrine\Common\DataFixtures\FixtureInterface;

    class LoadUserData implements FixtureInterface
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

Now you can begin adding the fixtures to a loader instance:

    use Doctrine\Common\DataFixtures\Loader;
    use MyDataFixtures\LoadUserData;

    $loader = new Loader();
    $loader->addFixture(new LoadUserData);

You can load a set of fixtures from a directory as well:

    $loader->loadFromDirectory('/path/to/MyDataFixtures');

You can get the added fixtures using the getFixtures() method:

    $fixtures = $loader->getFixtures();

Now you can easily execute the fixtures:

    use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
    use Doctrine\Common\DataFixtures\Purger\ORMPurger;

    $purger = new ORMPurger();
    $executor = new ORMExecutor($em, $purger);
    $executor->execute($loader->getFixtures());

If you want to append the fixtures instead of purging before loading then pass true
to the 2nd argument of execute:

    $executor->execute($loader->getFixtures(), true);

## Sharing objects between fixtures

In case if fixture objects have relations to other fixtures, it is now possible
to easily add a reference to that object by name and later reference it to form
a relation. Here is an example fixtures for **Role** and **User** relation

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\Persistence\ObjectManager;

    class LoadUserRoleData extends AbstractFixture
    {
        public function load(ObjectManager $manager)
        {
            $adminRole = new Role();
            $adminRole->setName('admin');
            // store reference to admin role for User relation to Role
            $this->addReference('admin-role', $adminRole);
            
            $anonymousRole = new Role;
            $anonymousRole->setName('anonymous');
    
            $manager->persist($adminRole);
            $manager->persist($anonymousRole);
            $manager->flush();
        }
    }
    
And the **User** data loading fixture:
**Notice**: that stored references may not be at the **managed** state in UnitOfWork.
Use $manager->merge($object); function to restore the state of object

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\Persistence\ObjectManager;

    class LoadUserData extends AbstractFixture
    {
        public function load(ObjectManager $manager)
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');
            // load the stored reference, notice that, its state can get unmanaged
            // so we merge it into identity map of UnitOfWork
            $user->setRole(
                $manager->merge($this->getReference('admin-role'))
            );

            $manager->persist($user);
            $manager->flush();
            
            // store reference of admin-user for other Fixtures
            $this->addReference('admin-user', $user);
        }
    }

**Notice** that the fixture loading order is important! To handle it manually
implement the OrderedFixtureInterface and set the order:

    namespace MyDataFixtures;

    use Doctrine\Common\DataFixtures\AbstractFixture;
    use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
    use Doctrine\Common\Persistence\ObjectManager;

    class MyFixture extends AbstractFixture implements OrderedFixtureInterface
    {
        public function load(ObjectManager $manager)
        {}
        
        public function getOrder()
        {
            return 10; // number in which order to load fixtures
        }
    }

**Notice** the ordering is relevant to Loader class.

## Running the tests:

PHPUnit 3.5 or newer together with Mock_Object package is required.
To setup and run tests follow these steps:

- go to the root directory of data-fixtures
- run: **git submodule init**
- run: **git submodule update**
- copy the phpunit config **cp phpunit.xml.dist phpunit.xml**
- run: **phpunit**
