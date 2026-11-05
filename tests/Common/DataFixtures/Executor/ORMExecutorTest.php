<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Closure;
use Doctrine\Common\DataFixtures\Executor\MultipleTransactionORMExecutor;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Tests\Common\DataFixtures\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test Fixture executor.
 */
class ORMExecutorTest extends BaseTestCase
{
    public function testExecuteWithNoPurge(): void
    {
        $em     = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('setEntityManager')
            ->with($em);
        $executor = new ORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], true);
    }

    public function testExecuteSingleTransactionsCountTransactionalCalls(): void
    {
        $em = $this->getMockEntityManager();
        $em->method('getEventManager')->willReturn($this->createStub(EventManager::class));
        // We call wrapInTransaction once for purge and for the fixtures (load)
        $em->expects($this->once())->method('wrapInTransaction')->with(self::isInstanceOf(Closure::class));

        $executor = new ORMExecutor($em);
        $fixture  = $this->createStub(FixtureInterface::class);
        @$executor->execute([$fixture, $fixture]);
    }

    public function testExecuteWithPurge(): void
    {
        $em     = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge');
        $executor = new ORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], false);
    }

    public function testExecuteTransaction(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $executor = new ORMExecutor($em);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], true);
    }

    public function testCustomLegacyEntityManager(): void
    {
        $em = $this->getMockEntityManager();
        $em->method('getEventManager')->willReturn($this->createStub(EventManager::class));
        $em->expects($this->once())->method('wrapInTransaction')->with(self::isInstanceOf(Closure::class));

        $executor = new ORMExecutor($em);
        @$executor->execute([]);
    }

    public function testExecuteMultipleTransactionsWithNoPurge(): void
    {
        $em     = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('setEntityManager')
            ->with($em);
        $executor = new MultipleTransactionORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], true);
    }

    public function testExecuteMultipleTransactionsWithPurge(): void
    {
        $em     = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge');
        $executor = new MultipleTransactionORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], false);
    }

    public function testExecuteMultipleTransactionsCountTransactionalCalls(): void
    {
        $em = $this->getMockEntityManager();
        $em->method('getEventManager')->willReturn($this->createStub(EventManager::class));
        // We call wrapInTransaction once for purge and twice for the fixtures (load)
        $em->expects($this->exactly(3))->method('wrapInTransaction')->with(self::isInstanceOf(Closure::class));

        $executor = new MultipleTransactionORMExecutor($em);
        $fixture  = $this->createStub(FixtureInterface::class);
        @$executor->execute([$fixture, $fixture]);
    }

    private function getMockEntityManager(): EntityManagerInterface&MockObject
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    private function getMockFixture(): FixtureInterface&MockObject
    {
        return $this->createMock(FixtureInterface::class);
    }

    private function getMockPurger(): ORMPurgerInterface&MockObject
    {
        return $this->createMock(ORMPurgerInterface::class);
    }
}
