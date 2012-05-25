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

namespace Doctrine\Common\DataFixtures\Event\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

/**
 * Reference Listener populates identities for
 * stored references
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
final class MongoDBReferenceListener implements EventSubscriber
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * Initialize listener
     *
     * @param ReferenceRepository $referenceRepository
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
            'postPersist'
        );
    }

    /**
     * Populates identities for stored references
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getDocument();
        if (($name = $this->referenceRepository->getReferenceName($object)) !== false) {
            $identity = $args->getDocumentManager()
                ->getUnitOfWork()
                ->getDocumentIdentifier($object);
            $this->referenceRepository->setReferenceIdentity($name, $identity);
        }
    }
}
