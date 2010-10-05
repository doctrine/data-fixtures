# Doctrine Data Fixtures Extension

This extension aims to provide a simple way to manage and execute the loading of data fixtures
for the Doctrine ORM or ODM. You can write fixture classes by implementing the
Doctrine\Common\DataFixtures\FixtureInterface interface:

    namespace MyDataFixtures;

    use Doctrine\ORM\EntityManager;
    use Doctrine\Common\DataFixtures\FixtureInterface;

    class LoadUserData implements FixtureInterface
    {
        public function load($manager)
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

    $purger = new Purger();
    $executor = new ORMExecutor($em, $purger);
    $executor->execute($loader->getFixtures());

If you want to append the fixtures instead of purging before loading then pass false
to the 2nd argument of execute:

    $executor->execute($loader->getFixtures(), true);