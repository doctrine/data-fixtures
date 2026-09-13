<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use ArgumentCountError;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use TestFixtures\MyFixture1;
use TestFixtures\NotAFixture;

/**
 * Test fixtures loader.
 */
class LoaderTest extends BaseTestCase
{
    public function testLoadFromDirectory(): void
    {
        $loader = new Loader();
        $loader->addFixture(new DummyFixtureOne());
        $loader->addFixture(new DummyFixtureTwo());
        $loader->addFixture(new SharedDummyFixture());

        $this->assertCount(3, $loader->getFixtures());

        $loader->loadFromDirectory(__DIR__ . '/TestFixtures');
        $this->assertCount(7, $loader->getFixtures());
        $this->assertTrue($loader->isTransient(NotAFixture::class));
        $this->assertFalse($loader->isTransient(MyFixture1::class));
    }

    public function testLoadFromFile(): void
    {
        $loader = new Loader();
        $loader->addFixture(new DummyFixtureOne());
        $loader->addFixture(new DummyFixtureTwo());
        $loader->addFixture(new SharedDummyFixture());

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

    /**
     * Test that an error is expected when adding a fixture which requires constructor arguments
     */
    public function testAddFixtureWithDependencyError(): void
    {
        $loader = new Loader();
        $this->expectException(ArgumentCountError::class);
        $loader->addFixture(new FixtureWithDependency());
    }

    /**
     * Test that a fixture dependency is not instantiated if it has already been added
     */
    public function testAddFixtureWithDependencyPreLoaded(): void
    {
        $fixtureWithConstructor = new FixtureWithConstructorArgs('test');
        $fixtureWithDependency  = new FixtureWithDependency();

        $loader = new Loader();
        $loader->addFixture($fixtureWithConstructor);
        $loader->addFixture($fixtureWithDependency);

        $this->assertSame([$fixtureWithConstructor, $fixtureWithDependency], $loader->getFixtures());
        $this->assertSame('test', $fixtureWithConstructor->getRequiredArgument());
    }
}

final class DummyFixtureOne implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}

final class DummyFixtureTwo implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}

final class SharedDummyFixture implements SharedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function setReferenceRepository(ReferenceRepository $referenceRepository): void
    {
    }
}

final class FixtureWithDependency implements DependentFixtureInterface, FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }

    public function getDependencies(): array
    {
        return [FixtureWithConstructorArgs::class];
    }
}

final class FixtureWithConstructorArgs implements FixtureInterface
{
    public function __construct(private readonly string $requiredArgument)
    {
    }

    public function load(ObjectManager $manager): void
    {
    }

    public function getRequiredArgument(): string
    {
        return $this->requiredArgument;
    }
}
