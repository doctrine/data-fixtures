<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/** @internal */
trait ORMExecutorCommon
{
    /** @var EntityManager|EntityManagerDecorator */
    private EntityManagerInterface $em;

    private EntityManagerInterface $originalManager;
    private ORMReferenceListener $listener;

    public function __construct(EntityManagerInterface $em, ORMPurgerInterface|null $purger = null)
    {
        $this->originalManager = $em;
        // Make sure, wrapInTransaction() exists on the EM.
        // To be removed when dropping support for ORM 2
        $this->em = $em instanceof EntityManager || $em instanceof EntityManagerDecorator
            ? $em
            : new class ($em) extends EntityManagerDecorator {
            };

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
        return $this->originalManager;
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
