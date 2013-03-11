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

namespace Doctrine\Test\Fixture\Reference;

use Doctrine\Fixture\Reference\DoctrineCacheReferenceRepository;
use Doctrine\Fixture\Reference\ReferenceRepositoryEventSubscriber;
use Doctrine\Fixture\Event\FixtureEvent;
use Doctrine\Common\Cache\ArrayCache;

/**
 * ReferenceRepositoryEventSubscriber tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ReferenceRepositoryEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Reference\DoctrineCacheReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var \Doctrine\Fixture\Reference\ReferenceRepositoryEventSubscriber
     */
    private $subscriber;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->referenceRepository = new DoctrineCacheReferenceRepository(new ArrayCache());
        $this->subscriber          = new ReferenceRepositoryEventSubscriber($this->referenceRepository);
    }

    public function testGetSubscribedEvents()
    {
        $expected = array(
            'purge',
            'import'
        );

        $this->assertEquals($expected, $this->subscriber->getSubscribedEvents());
    }

    public function testImport()
    {
        $mockFixture = $this->getMockBuilder('Doctrine\Test\Mock\Unassigned\FixtureA')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($this->once())
                 ->method('setReferenceRepository')
                 ->with($this->equalTo($this->referenceRepository));

        $event = new FixtureEvent($mockFixture);

        $this->subscriber->import($event);
    }

    public function testImportNoInterface()
    {
        $mockFixture = $this->getMockBuilder('Doctrine\Test\Mock\Unassigned\FixtureB')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($this->never())
                 ->method('setReferenceRepository');

        $event = new FixtureEvent($mockFixture);

        $this->subscriber->import($event);
    }

    public function testPurge()
    {
        $mockFixture = $this->getMockBuilder('Doctrine\Test\Mock\Unassigned\FixtureA')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($this->once())
                 ->method('setReferenceRepository')
                 ->with($this->equalTo($this->referenceRepository));

        $event = new FixtureEvent($mockFixture);

        $this->subscriber->purge($event);
    }

    public function testPurgeNoInterface()
    {
        $mockFixture = $this->getMockBuilder('Doctrine\Test\Mock\Unassigned\FixtureB')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($this->never())
                 ->method('setReferenceRepository');

        $event = new FixtureEvent($mockFixture);

        $this->subscriber->purge($event);
    }

}