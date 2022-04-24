<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Closure;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
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
