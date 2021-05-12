<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use TestFixtures\MyFixture1;
use TestFixtures\NotAFixture;

/**
 * Test fixtures loader.
 */
class LoaderTest extends BaseTest
{
    public function testLoadFromDirectory(): void
    {
        $loader = new Loader();
        $loader->addFixture($this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock1')->getMock());
        $loader->addFixture($this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock2')->getMock());
        $loader->addFixture($this->getMockBuilder(SharedFixtureInterface::class)->setMockClassName('Mock3')->getMock());

        $this->assertCount(3, $loader->getFixtures());

        $loader->loadFromDirectory(__DIR__ . '/TestFixtures');
        $this->assertCount(8, $loader->getFixtures());
        $this->assertTrue($loader->isTransient(NotAFixture::class));
        $this->assertFalse($loader->isTransient(MyFixture1::class));
    }

    public function testLoadFromFile(): void
    {
        $loader = new Loader();
        $loader->addFixture($this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock1')->getMock());
        $loader->addFixture($this->getMockBuilder(FixtureInterface::class)->setMockClassName('Mock2')->getMock());
        $loader->addFixture($this->getMockBuilder(SharedFixtureInterface::class)->setMockClassName('Mock3')->getMock());

        $this->assertCount(3, $loader->getFixtures());

        $loader->loadFromFile(__DIR__ . '/TestFixtures/MyFixture1.php');
        $this->assertCount(4, $loader->getFixtures());
        $loader->loadFromFile(__DIR__ . '/TestFixtures/NotAFixture.php');
        $this->assertCount(4, $loader->getFixtures());
        $loader->loadFromFile(__DIR__ . '/TestFixtures/MyFixture2.php');
        $this->assertCount(5, $loader->getFixtures());
        $this->assertTrue($loader->isTransient(NotAFixture::class));
        $this->assertFalse($loader->isTransient(MyFixture1::class));
    }

    public function testGetFixture(): void
    {
        $loader = new Loader();
        $loader->loadFromFile(__DIR__ . '/TestFixtures/MyFixture1.php');

        $fixture = $loader->getFixture(MyFixture1::class);

        $this->assertInstanceOf(MyFixture1::class, $fixture);
    }
}
