<?php

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Tests\Common\DataFixtures\BaseTest;

/**
 * Test Fixture executor.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ORMExecutorTest extends BaseTest
{
    public function testExecuteWithNoPurge()
    {
        $em = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('setEntityManager')
            ->with($em);
        $executor = new ORMExecutor($em, $purger);
        $fixture = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], true);
    }

    public function testExecuteWithPurge()
    {
        $em = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge')
            ->will($this->returnValue(null));
        $executor = new ORMExecutor($em, $purger);
        $fixture = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute([$fixture], false);
    }

    public function testExecuteTransaction()
    {
        $em = $this->getMockSqliteEntityManager();
        $executor = new ORMExecutor($em);
        $fixture = $this->getMockFixture();
        $executor->execute([$fixture], true);
    }

    private function getMockFixture()
    {
        return $this->createMock(FixtureInterface::class);
    }

    private function getMockPurger()
    {
        return $this->createMock(ORMPurger::class);
    }
}
