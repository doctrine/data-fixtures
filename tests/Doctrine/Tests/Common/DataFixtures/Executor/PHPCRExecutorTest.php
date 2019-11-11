<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Tests\Common\DataFixtures\BaseTest;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use Throwable;
use function class_exists;

/**
 * Tests for {@see \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor}
 *
 * @covers \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor
 */
class PHPCRExecutorTest extends BaseTest
{
    public function testExecuteSingleFixtureWithNoPurge()
    {
        $dm       = $this->getDocumentManager();
        $executor = new PHPCRExecutor($dm);
        $fixture  = $this->getMockFixture();

        $fixture->expects($this->once())->method('load')->with($dm);
        $dm
            ->expects($this->once())
            ->method('transactional')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(static function ($callback) use ($dm) {
                return $callback($dm);
            }));

        $executor->execute([$fixture], true);
    }

    public function testExecuteMultipleFixturesWithNoPurge()
    {
        $dm       = $this->getDocumentManager();
        $executor = new PHPCRExecutor($dm);
        $fixture1 = $this->getMockFixture();
        $fixture2 = $this->getMockFixture();

        $fixture1->expects($this->once())->method('load')->with($dm);
        $fixture2->expects($this->once())->method('load')->with($dm);
        $dm
            ->expects($this->once())
            ->method('transactional')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(static function ($callback) use ($dm) {
                return $callback($dm);
            }));

        $executor->execute([$fixture1, $fixture2], true);
    }

    public function testExecuteFixtureWithPurge()
    {
        $dm       = $this->getDocumentManager();
        $purger   = $this->getPurger();
        $executor = new PHPCRExecutor($dm, $purger);
        $fixture  = $this->getMockFixture();

        $fixture->expects($this->once())->method('load')->with($dm);
        $dm
            ->expects($this->once())
            ->method('transactional')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(static function ($callback) use ($dm) {
                return $callback($dm);
            }));
        $purger->expects($this->once())->method('purge');

        $executor->execute([$fixture], false);
    }

    public function testExecuteFixtureWithoutPurge()
    {
        $dm       = $this->getDocumentManager();
        $purger   = $this->getPurger();
        $executor = new PHPCRExecutor($dm, $purger);
        $fixture  = $this->getMockFixture();

        $fixture->expects($this->once())->method('load')->with($dm);
        $dm
            ->expects($this->once())
            ->method('transactional')
            ->with($this->isType('callable'))
            ->will($this->returnCallback(static function ($callback) use ($dm) {
                return $callback($dm);
            }));
        $purger->expects($this->never())->method('purge');

        $executor->execute([$fixture], true);
    }

    public function testFailedTransactionalStopsPurgingAndFixtureLoading()
    {
        $dm        = $this->getDocumentManager();
        $purger    = $this->getPurger();
        $executor  = new PHPCRExecutor($dm, $purger);
        $fixture   = $this->getMockFixture();
        $exception = new Exception();

        $fixture->expects($this->never())->method('load');
        $dm->expects($this->once())->method('transactional')->will($this->throwException($exception));
        $purger->expects($this->never())->method('purge');

        try {
            $executor->execute([$fixture], true);
        } catch (Throwable $caughtException) {
            $this->assertSame($exception, $caughtException);
        }
    }

    /**
     * @return PHPCRPurger|PHPUnit_Framework_MockObject_MockObject
     */
    private function getPurger()
    {
        return $this->createMock(PHPCRPurger::class);
    }

    /**
     * @return DocumentManager|PHPUnit_Framework_MockObject_MockObject
     */
    private function getDocumentManager()
    {
        if (! class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Missing doctrine/phpcr-odm');
        }

        return $this
            ->getMockBuilder(DocumentManager::class)
            ->setMethods([
                'transactional',
                'flush',
                'clear',
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return FixtureInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFixture()
    {
        return $this->createMock(FixtureInterface::class);
    }
}
