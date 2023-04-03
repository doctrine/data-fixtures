<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Test Fixture interface.
 */
class FixtureTest extends BaseTestCase
{
    public function testFixtureInterface(): void
    {
        $em      = $this->createMock(ObjectManager::class);
        $fixture = new MyFixture2();
        $fixture->load($em);

        self::assertTrue($fixture->loaded);
    }
}

class MyFixture2 implements FixtureInterface
{
    public bool $loaded = false;

    public function load(ObjectManager $manager): void
    {
        $this->loaded = true;
    }
}
