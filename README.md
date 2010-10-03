# Doctrine2 ORM Data Fixtures Extension

This extension aims to provide a simple way to manage and execute the loading of data fixtures
for the Doctrine2 Object Relational Mapper. You can write fixture classes by implementing
the Doctrine\ORM\DataFixtures\Fixture interface:

    namespace MyDataFixtures;

    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\DataFixtures\Fixture;

    class LoadUserData implements Fixture
    {
        public function load(EntityManager $em)
        {
            $user = new User();
            $user->setUsername('jwage');
            $user->setPassword('test');

            $em->persist($user);
            $em->flush();
        }
    }

Now you can begin adding the fixtures to a loader instance:

    use Doctrine\ORM\DataFixtures\Loader;
    use MyDataFixtures\LoadUserData;

    $loader = new Loader();
    $loader->addFixture(new LoadUserData);

You can load a set of fixtures from a directory as well:

    $loader->loadFromDirectory('/path/to/MyDataFixtures');

You can get the added fixtures using the getFixtures() method:

    $fixtures = $loader->getFixtures();

Now you can easily execute the fixtures:

    use Doctrine\ORM\DataFixtures\Executor;

    $purger = new Purger();
    $executor = new Executor($em, $purger);
    $executor->execute($loader->getFixtures());

If you want to append the fixtures instead of purging before loading then pass false
to the 2nd argument of execute:

    $executor->execute($loader->getFixtures(), true);