<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Closure;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Tests\Common\DataFixtures\BaseTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test Fixture executor.
 */
class ORMExecutorTest extends BaseTest
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

    public function testExecuteWithNoPurgeCountTransactionalCalls(): void
    {
        $em          = $this->getMockEntityManager();
        $purger      = $this->getMockPurger();
        $eventManager = new EventManager();

        $em->method('getEventManager')
            ->willReturn($eventManager);

        // We call transactional once for the pure and the fixtures (load)
        $em->expects($this->once())
            ->method('transactional');

        $executor = new ORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $executor->execute([$fixture, $fixture], false);
    }

    public function testExecuteWithPurge(): void
    {
        $em     = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge')
            ->will($this->returnValue(null));
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
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getEventManager')->willReturn($this->createMock(EventManager::class));
        $em->expects($this->once())->method('transactional')->with(self::isInstanceOf(Closure::class));

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
        $executor = new ORMExecutor($em, $purger, false);
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
            ->method('purge')
            ->will($this->returnValue(null));
        $executor = new ORMExecutor($em, $purger, false);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], false);
    }

    public function testExecuteMultipleTransactionsWithPurgeCountTransactionalCalls(): void
    {
        $em          = $this->getMockEntityManager();
        $purger      = $this->getMockPurger();
        $connection  = $this->getMockConnection();
        $eventManager = new EventManager();

        $em->method('getEventManager')
            ->willReturn($eventManager);

        $em->method('getConnection')
            ->willReturn($connection);

        // We call transactional once for purge and twice for the fixtures (load)
        $connection->expects($this->exactly(3))
            ->method('transactional');

        $executor = new ORMExecutor($em, $purger, false);
        $fixture  = $this->getMockFixture();
        $executor->execute([$fixture, $fixture], false);
    }

    /**
     * @return EntityManagerInterface&MockObject
     */
    private function getMockEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManager::class);
    }

    /**
     * @return Connection&MockObject
     */
    private function getMockConnection(): Connection
    {
        return $this->createMock(Connection::class);
    }

    /**
     * @return FixtureInterface&MockObject
     */
    private function getMockFixture(): FixtureInterface
    {
        return $this->createMock(FixtureInterface::class);
    }

    /**
     * @return ORMPurger&MockObject
     */
    private function getMockPurger(): ORMPurger
    {
        return $this->createMock(ORMPurger::class);
    }
}
