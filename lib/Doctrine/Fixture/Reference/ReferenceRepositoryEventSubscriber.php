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

namespace Doctrine\Fixture\Reference;

use Doctrine\Common\EventSubscriber;
use Doctrine\Fixture\Event\FixtureEvent;
use Doctrine\Fixture\Event\ImportFixtureEventListener;
use Doctrine\Fixture\Event\PurgeFixtureEventListener;

/**
 * Reference Repository Event Subscriber.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ReferenceRepositoryEventSubscriber implements
    EventSubscriber,
    ImportFixtureEventListener,
    PurgeFixtureEventListener
{
    /**
     * @var \Doctrine\Fixture\Reference\ReferenceRepository
     */
    private $referenceRepository;

    /**
     * Constructor.
     *
     * @param \Doctrine\Fixture\Reference\ReferenceRepository $referenceRepository
     */
    public function __construct(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            ImportFixtureEventListener::IMPORT,
            PurgeFixtureEventListener::PURGE,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function purge(FixtureEvent $event)
    {
        $fixture = $event->getFixture();

        if ( ! ($fixture instanceof ReferenceRepositoryFixture)) {
            return;
        }

        $fixture->setReferenceRepository($this->referenceRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function import(FixtureEvent $event)
    {
        $fixture = $event->getFixture();

        if ( ! ($fixture instanceof ReferenceRepositoryFixture)) {
            return;
        }

        $fixture->setReferenceRepository($this->referenceRepository);
    }
}

