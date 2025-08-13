<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\DryRunORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Tests\Common\DataFixtures\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

class DryRunORMExecutorTest extends BaseTestCase
{
    public function testExecuteWithNoPurge(): void
    {
        $em = $this->getMockEntityManager();
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('rollBack');
        $em->expects($this->once())->method('close');

        $purger = $this->getMockPurger();
        $purger->expects($this->never())
            ->method('purge');

        $executor = new DryRunORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], true);
    }

    public function testExecuteWithPurge(): void
    {
        $em = $this->getMockEntityManager();
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('rollBack');
        $em->expects($this->once())->method('close');

        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge');

        $executor = new DryRunORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], false);
    }

    public function testExecuteRollbackOnException(): void
    {
        $em = $this->getMockEntityManager();
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->never())->method('flush');
        $em->expects($this->once())->method('rollBack');
        $em->expects($this->once())->method('close');

        $purger = $this->getMockPurger();
        $purger->expects($this->never())
            ->method('purge');

        $executor = new DryRunORMExecutor($em, $purger);
        $fixture  = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);
        $executor->execute([$fixture], true);
    }

    private function getMockEntityManager(): EntityManagerInterface&MockObject
    {
        $em = $this->getMockSqliteEntityManager();

        return $this->getMockBuilder(EntityManager::class)
            ->setConstructorArgs([
                $em->getConnection(),
                $em->getConfiguration(),
                $em->getEventManager(),
            ])
            ->onlyMethods(['beginTransaction', 'flush', 'rollBack', 'close'])
            ->getMock();
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
