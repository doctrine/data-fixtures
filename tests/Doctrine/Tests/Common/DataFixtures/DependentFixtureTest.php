<?php

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Exception\CircularReferenceException;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use RuntimeException;

/**
 * Test Fixture ordering by dependencies.
 *
 * @author Gustavo Adrian <comfortablynumb84@gmail.com>
 */
class DependentFixtureTest extends BaseTest
{
    public function test_orderFixturesByDependencies_orderClassesWithASingleParent()
    {
        $loader = new Loader();
        $loader->addFixture(new DependentFixture3);
        $loader->addFixture(new DependentFixture1);
        $loader->addFixture(new DependentFixture2);
        $loader->addFixture(new BaseParentFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(4, $orderedFixtures);
        $this->assertInstanceOf(BaseParentFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture2::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture3::class, array_shift($orderedFixtures));
    }

    public function test_orderFixturesByDependencies_orderClassesWithAMultipleParents()
    {
        $loader = new Loader();

        $addressFixture         = new AddressFixture();
        $contactMethodFixture   = new ContactMethodFixture();
        $contactFixture         = new ContactFixture();
        $baseParentFixture      = new BaseParentFixture1();
        $countryFixture         = new CountryFixture();
        $stateFixture           = new StateFixture();

        $loader->addFixture($addressFixture);
        $loader->addFixture($contactMethodFixture);
        $loader->addFixture($contactFixture);
        $loader->addFixture($baseParentFixture);
        $loader->addFixture($countryFixture);
        $loader->addFixture($stateFixture);

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(6, $orderedFixtures);

        $contactFixtureOrder = array_search($contactFixture, $orderedFixtures);
        $contactMethodFixtureOrder = array_search($contactMethodFixture, $orderedFixtures);
        $addressFixtureOrder = array_search($addressFixture, $orderedFixtures);
        $countryFixtureOrder = array_search($countryFixture, $orderedFixtures);
        $stateFixtureOrder = array_search($stateFixture, $orderedFixtures);
        $baseParentFixtureOrder = array_search($baseParentFixture, $orderedFixtures);

        // Order of fixtures is not exact. We need to test, however, that dependencies are
        // indeed satisfied

        // BaseParentFixture1 has no dependencies, so it will always be first in this case
        $this->assertEquals($baseParentFixtureOrder, 0);

        $this->assertGreaterThan($contactMethodFixtureOrder, $contactFixtureOrder);
        $this->assertGreaterThan($addressFixtureOrder, $contactFixtureOrder);
        $this->assertGreaterThan($countryFixtureOrder, $contactFixtureOrder);
        $this->assertGreaterThan($stateFixtureOrder, $contactFixtureOrder);
        $this->assertGreaterThan($contactMethodFixtureOrder, $contactFixtureOrder);

        $this->assertGreaterThan($stateFixtureOrder, $addressFixtureOrder);
        $this->assertGreaterThan($countryFixtureOrder, $addressFixtureOrder);
    }


    public function test_orderFixturesByDependencies_circularReferencesMakeMethodThrowCircularReferenceException()
    {
        $loader = new Loader();

        $loader->addFixture(new CircularReferenceFixture3);
        $loader->addFixture(new CircularReferenceFixture);
        $loader->addFixture(new CircularReferenceFixture2);

        $this->expectException(CircularReferenceException::class);

        $loader->getFixtures();
    }

    public function test_orderFixturesByDependencies_fixturesCantHaveItselfAsParent()
    {
        $loader = new Loader();

        $loader->addFixture(new FixtureWithItselfAsParent);

        $this->expectException(InvalidArgumentException::class);

        $loader->getFixtures();
    }

    public function test_inCaseThereAreFixturesOrderedByNumberAndByDependenciesBothOrdersAreExecuted()
    {
        $loader = new Loader();
        $loader->addFixture(new OrderedByNumberFixture1);
        $loader->addFixture(new OrderedByNumberFixture3);
        $loader->addFixture(new OrderedByNumberFixture2);
        $loader->addFixture(new DependentFixture3);
        $loader->addFixture(new DependentFixture1);
        $loader->addFixture(new DependentFixture2);
        $loader->addFixture(new BaseParentFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(7, $orderedFixtures);
        $this->assertInstanceOf(OrderedByNumberFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(OrderedByNumberFixture2::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(OrderedByNumberFixture3::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(BaseParentFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture2::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture3::class, array_shift($orderedFixtures));
    }

    public function test_inCaseAFixtureHasAnUnexistentDependencyOrIfItWasntLoaded_throwsException()
    {
        $loader = new Loader();
        $loader->addFixture(new FixtureWithUnexistentDependency);

        $this->expectException(RuntimeException::class);

        $loader->getFixtures();
    }

    public function test_inCaseGetFixturesReturnsDifferentResultsEachTime()
    {
        $loader = new Loader();
        $loader->addFixture(new DependentFixture1);
        $loader->addFixture(new BaseParentFixture1);

        // Intentionally calling getFixtures() twice
        $loader->getFixtures();
        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(2, $orderedFixtures);
        $this->assertInstanceOf(BaseParentFixture1::class, array_shift($orderedFixtures));
        $this->assertInstanceOf(DependentFixture1::class, array_shift($orderedFixtures));
    }
}

class DependentFixture1 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [BaseParentFixture1::class];
    }
}

class DependentFixture2 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [DependentFixture1::class];
    }
}

class DependentFixture3 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [DependentFixture2::class];
    }
}

class BaseParentFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {}
}

class CountryFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [BaseParentFixture1::class];
    }
}

class StateFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [
            BaseParentFixture1::class,
            CountryFixture::class
        ];
    }
}

class AddressFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [
            BaseParentFixture1::class,
            CountryFixture::class,
            StateFixture::class
        ];
    }
}

class ContactMethodFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [BaseParentFixture1::class];
    }
}

class ContactFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [
            AddressFixture::class,
            ContactMethodFixture::class
        ];
    }
}

class CircularReferenceFixture implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [CircularReferenceFixture3::class];
    }
}

class CircularReferenceFixture2 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [CircularReferenceFixture::class];
    }
}

class CircularReferenceFixture3 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [CircularReferenceFixture2::class];
    }
}

class FixtureWithItselfAsParent implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return [FixtureWithItselfAsParent::class];
    }
}

class FixtureWithUnexistentDependency implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return ['UnexistentDependency'];
    }
}

class FixtureImplementingBothOrderingInterfaces implements FixtureInterface, OrderedFixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder()
    {
        return 1;
    }

    public function getDependencies()
    {
        return [FixtureWithItselfAsParent::class];
    }
}

class OrderedByNumberFixture1 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder()
    {
        return 1;
    }
}

class OrderedByNumberFixture2 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder()
    {
        return 5;
    }
}

class OrderedByNumberFixture3 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder()
    {
        return 10;
    }
}
