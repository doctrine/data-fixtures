<?php

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class MongoDBExecutor extends AbstractExecutor
{
    public function __construct(DocumentManager $dm, MongoDBPurger $purger = null)
    {
        $this->dm = $dm;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setDocumentManager($dm);
        }
        parent::__construct($dm);
        $this->listener = new MongoDBReferenceListener($this->referenceRepository);
        $dm->getEventManager()->addEventSubscriber($this->listener);
    }

    public function getObjectManager(): DocumentManager
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->dm->getEventManager()->removeEventListener(
            $this->listener->getSubscribedEvents(),
            $this->listener
        );

        $this->referenceRepository = $referenceRepository;
        $this->listener = new MongoDBReferenceListener($this->referenceRepository);
        $this->dm->getEventManager()->addEventSubscriber($this->listener);
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        if ($append === false) {
            $this->purge();
        }
        foreach ($fixtures as $fixture) {
            $this->load($this->dm, $fixture);
        }
    }
}
