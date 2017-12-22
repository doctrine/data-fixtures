<?php

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Test Fixture interface.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class FixtureTest extends BaseTest
{
    public function testFixtureInterface()
    {
        $em = $this->createMock(ObjectManager::class);
        $fixture = new MyFixture2();
        $fixture->load($em);

        self::assertTrue($fixture->loaded);
    }
}

class MyFixture2 implements FixtureInterface
{
    public $loaded = false;

    public function load(ObjectManager $manager)
    {
        $this->loaded = true;
    }
}
