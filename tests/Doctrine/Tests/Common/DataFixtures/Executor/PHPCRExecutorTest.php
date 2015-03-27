<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\PHPCRExecutor;
use Exception;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @covers \Doctrine\Common\DataFixtures\Executor\PHPCRExecutor
 */
class PHPCRExecutorTest extends PHPUnit_Framework_TestCase
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

        $executor->execute(array($fixture), true);
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

        $executor->execute(array($fixture1, $fixture2), true);
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

        $executor->execute(array($fixture), false);
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

        $executor->execute(array($fixture), true);
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
            $executor->execute(array($fixture), true);
        } catch (\Exception $caughtException) {
            $this->assertSame($exception, $caughtException);
        }
    }

    /**
     * @return \Doctrine\Common\DataFixtures\Purger\PHPCRPurger|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPurger()
    {
        return $this->getMock('Doctrine\Common\DataFixtures\Purger\PHPCRPurger', array(), array(), '', false);
    }

    /**
     * @return \Doctrine\ODM\PHPCR\DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDocumentManager()
    {
        $this->loadDocumentManagerClass();

        return $this->getMock(
            'Doctrine\ODM\PHPCR\DocumentManager',
            array(
                'transactional',
                'flush',
                'clear',
            ),
            array(),
            '',
            false
        );
    }

    /**
     * @return \Doctrine\Common\DataFixtures\FixtureInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFixture()
    {
        return $this->getMock('Doctrine\Common\DataFixtures\FixtureInterface', array(), array(), '', false);
    }

    /**
     * Ensures that the {@see \Doctrine\ODM\PHPCR\DocumentManager} class exists
     */
    private function loadDocumentManagerClass()
    {

        if (class_exists('Doctrine\ODM\PHPCR\DocumentManager')) {
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
