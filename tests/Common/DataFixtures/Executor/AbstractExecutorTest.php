<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class AbstractExecutorTest extends TestCase
{
    use VerifyDeprecations;

    public function testLogOnLoad(): void
    {
        $this->expectNoDeprecationWithIdentifier('https://github.com/doctrine/data-fixtures/pull/462');

        $logger   = new TestLogger();
        $executor = $this->bootstrapExecutor();
        $executor->setLogger($logger);

        $executor->load($this->createStub(ObjectManager::class), new DummyFixture());

        self::assertTrue($logger->hasDebugThatContains('loading Doctrine\Tests\Common\DataFixtures\Executor\DummyFixture'));

        $executor->load($this->createStub(ObjectManager::class), new DummyOrderedFixture());

        self::assertTrue($logger->hasDebugThatContains('loading [42] Doctrine\Tests\Common\DataFixtures\Executor\DummyOrderedFixture'));
    }

    public function testLogOnPurge(): void
    {
        $this->expectNoDeprecationWithIdentifier('https://github.com/doctrine/data-fixtures/pull/462');

        $logger   = new TestLogger();
        $executor = $this->bootstrapExecutor();
        $executor->setLogger($logger);
        $executor->setPurger($this->createStub(PurgerInterface::class));

        $executor->purge();

        self::assertTrue($logger->hasDebugThatContains('purging database'));
    }

    private function bootstrapExecutor(): AbstractExecutor
    {
        return new class ($this->createStub(ObjectManager::class)) extends AbstractExecutor {
            public function execute(array $fixtures, bool $append = false): void
            {
                $this->logger->debug('Executed!');
            }
        };
    }
}

class DummyFixture implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}

class DummyOrderedFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function getOrder(): int
    {
        return 42;
    }

    public function load(ObjectManager $manager): void
    {
    }
}
