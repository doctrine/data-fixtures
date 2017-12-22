<?php

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Tests\Common\DataFixtures\BaseTest;
use Exception;

/**
 * Tests for {@see \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
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
            ->will($this->returnCallback(function ($callback) use ($dm) {
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
            ->will($this->returnCallback(function ($callback) use ($dm) {
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
            ->will($this->returnCallback(function ($callback) use ($dm) {
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
            ->will($this->returnCallback(function ($callback) use ($dm) {
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
        } catch (\Exception $caughtException) {
            $this->assertSame($exception, $caughtException);
        }
    }

    /**
     * @return PHPCRPurger|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPurger()
    {
        return $this->createMock(PHPCRPurger::class);
    }

    /**
     * @return DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDocumentManager()
    {
        $this->loadDocumentManagerClass();

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
     * @return FixtureInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFixture()
    {
        return $this->createMock(FixtureInterface::class);
    }

    /**
     * Ensures that the {@see \Doctrine\ODM\PHPCR\DocumentManager} class exists
     */
    private function loadDocumentManagerClass()
    {

        if (class_exists(DocumentManager::class)) {
            return;
        }

        // hold my beer while I do some mocking
        eval(
        <<<'PHP'
namespace Doctrine\ODM\PHPCR;

class DocumentManager implements \Doctrine\Common\Persistence\ObjectManager
{
    public function find($className, $id) {}
    public function persist($object) {}
    public function remove($object) {}
    public function merge($object) {}
    public function clear($objectName = null) {}
    public function detach($object) {}
    public function refresh($object) {}
    public function flush() {}
    public function getRepository($className) {}
    public function getClassMetadata($className) {}
    public function getMetadataFactory() {}
    public function initializeObject($obj) {}
    public function contains($object) {}
}
PHP
        );
    }
}
