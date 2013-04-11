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

namespace Doctrine\Test\Fixture\Executor;

use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Executor\BulkExecutorEventSubscriber;
use Doctrine\Fixture\Event\BulkFixtureEvent;
use Doctrine\Fixture\Event\BulkImportFixtureEventListener;
use Doctrine\Fixture\Event\BulkPurgeFixtureEventListener;
use Doctrine\Fixture\Event\ImportFixtureEventListener;
use Doctrine\Fixture\Event\PurgeFixtureEventListener;
use Doctrine\Test\Mock\Unassigned\FixtureA;

/**
 * BulkExecutorEventSubscriber tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class BulkExecutorEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Executor\BulkExecutorEventSubscriber
     */
    private $subscriber;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subscriber = new BulkExecutorEventSubscriber();
    }

    public function testGetSubscribedEvents()
    {
        $subscribedEventList = $this->subscriber->getSubscribedEvents();

        $this->assertContains(BulkImportFixtureEventListener::BULK_IMPORT, $subscribedEventList);
        $this->assertContains(BulkPurgeFixtureEventListener::BULK_PURGE, $subscribedEventList);
    }

    public function testImport()
    {
        $configuration = new Configuration();
        $fixture       = new FixtureA();
        $event         = new BulkFixtureEvent($configuration, array($fixture));
        $eventManager  = $this->createEventManagerMock();

        $configuration->setEventManager($eventManager);

        $this->subscriber->bulkImport($event);
    }

    public function testPurge()
    {
        $configuration = new Configuration();
        $fixture       = new FixtureA();
        $event         = new BulkFixtureEvent($configuration, array($fixture));
        $eventManager  = $this->createEventManagerMock();

        $configuration->setEventManager($eventManager);

        $this->subscriber->bulkPurge($event);
    }

    private function createEventManagerMock()
    {
        $mock = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
             ->method('dispatchEvent');

        return $mock;
    }
}
