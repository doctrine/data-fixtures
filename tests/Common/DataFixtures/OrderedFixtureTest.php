<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Test Fixture ordering.
 */
class OrderedFixtureTest extends BaseTestCase
{
    public function testFixtureOrder(): void
    {
        $loader = new Loader();
        $loader->addFixture(new OrderedFixture1());
        $loader->addFixture(new OrderedFixture2());
        $loader->addFixture(new OrderedFixture3());
        $loader->addFixture(new BaseFixture1());

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(4, $orderedFixtures);
        $this->assertInstanceOf(BaseFixture1::class, $orderedFixtures[0]);
        $this->assertInstanceOf(OrderedFixture2::class, $orderedFixtures[1]);
        $this->assertInstanceOf(OrderedFixture1::class, $orderedFixtures[2]);
        $this->assertInstanceOf(OrderedFixture3::class, $orderedFixtures[3]);
    }
}

class OrderedFixture1 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function getOrder(): int
    {
        return 5;
    }
}

class OrderedFixture2 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function getOrder(): int
    {
        return 2;
    }
}

class OrderedFixture3 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function getOrder(): int
    {
        return 8;
    }
}

class BaseFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}
