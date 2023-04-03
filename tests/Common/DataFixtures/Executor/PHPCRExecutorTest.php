<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Tests\Common\DataFixtures\BaseTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

use function class_exists;

/**
 * Tests for {@see \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor}
 *
 * @covers \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor
 */
class PHPCRExecutorTest extends BaseTestCase
{
    public function testExecuteSingleFixtureWithNoPurge(): void
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

    public function testExecuteMultipleFixturesWithNoPurge(): void
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

    public function testExecuteFixtureWithPurge(): void
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

    public function testExecuteFixtureWithoutPurge(): void
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

    public function testFailedTransactionalStopsPurgingAndFixtureLoading(): void
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

    /** @return PHPCRPurger&MockObject */
    private function getPurger(): PHPCRPurger
    {
        return $this->createMock(PHPCRPurger::class);
    }

    /** @return DocumentManager&MockObject */
    private function getDocumentManager(): DocumentManager
    {
        if (! class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Missing doctrine/phpcr-odm');
        }

        return $this
            ->getMockBuilder(DocumentManager::class)
            ->addMethods([
                'transactional',
                'flush',
                'clear',
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /** @return FixtureInterface&MockObject */
    private function getMockFixture(): FixtureInterface
    {
        return $this->createMock(FixtureInterface::class);
    }
}
