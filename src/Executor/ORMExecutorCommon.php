<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;

/** @internal */
trait ORMExecutorCommon
{
    private ORMReferenceListener $listener;

    public function __construct(
        private EntityManagerInterface $em,
        ORMPurgerInterface|null $purger = null,
    ) {
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setEntityManager($em);
        }

        parent::__construct($em);

        $this->listener = new ORMReferenceListener($this->referenceRepository);
        $em->getEventManager()->addEventSubscriber($this->listener);
    }

    /**
     * Retrieve the EntityManagerInterface instance this executor instance is using.
     */
    public function getObjectManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function setReferenceRepository(ReferenceRepository $referenceRepository): void
    {
        $this->em->getEventManager()->removeEventListener(
            $this->listener->getSubscribedEvents(),
            $this->listener,
        );

        parent::setReferenceRepository($referenceRepository);

        $this->listener = new ORMReferenceListener($this->referenceRepository);
        $this->em->getEventManager()->addEventSubscriber($this->listener);
    }
}
