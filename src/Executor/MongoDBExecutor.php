<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurgerInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class responsible for executing data fixtures.
 */
final class MongoDBExecutor extends AbstractExecutor
{
    private MongoDBReferenceListener $listener;

    /**
     * Construct new fixtures loader instance.
     *
     * @param DocumentManager $dm DocumentManager instance used for persistence.
     */
    public function __construct(private DocumentManager $dm, MongoDBPurgerInterface|null $purger = null)
    {
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setDocumentManager($dm);
        }

        parent::__construct($dm);

        $this->listener = new MongoDBReferenceListener($this->referenceRepository);
        $dm->getEventManager()->addEventSubscriber($this->listener);
    }

    /**
     * Retrieve the DocumentManager instance this executor instance is using.
     */
    public function getObjectManager(): DocumentManager
    {
        return $this->dm;
    }

    public function setReferenceRepository(ReferenceRepository $referenceRepository): void
    {
        $this->dm->getEventManager()->removeEventListener(
            $this->listener->getSubscribedEvents(),
            $this->listener,
        );

        $this->referenceRepository = $referenceRepository;
        $this->listener            = new MongoDBReferenceListener($this->referenceRepository);
        $this->dm->getEventManager()->addEventSubscriber($this->listener);
    }

    /** @inheritDoc */
    public function execute(array $fixtures, bool $append = false): void
    {
        if ($append === false) {
            $this->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($this->dm, $fixture);
        }
    }
}
