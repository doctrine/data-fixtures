<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\Executor\Strategy\SingleTransactionStrategy;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class responsible for executing data fixtures.
 */
class ORMExecutor extends AbstractExecutor
{
    /** @var EntityManager|EntityManagerDecorator */
    private $em;

    /** @var EntityManagerInterface */
    private $originalManager;

    /** @var ORMReferenceListener */
    private $listener;

    /** @var ExecutorStrategy|null */
    private $executorStrategy;

    /**
     * @param EntityManagerInterface $em EntityManagerInterface instance used for persistence.
     * @psalm-param class-string<ExecutorStrategy> $executorStrategy
     */
    public function __construct(EntityManagerInterface $em, ?ORMPurgerInterface $purger = null, ?string $executorStrategy = null)
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

        $this->executorStrategy = $executorStrategy ? new $executorStrategy($this) : new SingleTransactionStrategy($this);

        parent::__construct($em);
        $this->listener = new ORMReferenceListener($this->referenceRepository);
        $em->getEventManager()->addEventSubscriber($this->listener);
    }

    /**
     * Retrieve the EntityManagerInterface instance this executor instance is using.
     *
     * @return EntityManagerInterface
     */
    public function getObjectManager()
    {
        return $this->originalManager;
    }

    /** @inheritDoc */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->em->getEventManager()->removeEventListener(
            $this->listener->getSubscribedEvents(),
            $this->listener
        );

        $this->referenceRepository = $referenceRepository;
        $this->listener            = new ORMReferenceListener($this->referenceRepository);
        $this->em->getEventManager()->addEventSubscriber($this->listener);
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        $this->executorStrategy->execute($this->em, $fixtures, $append);
    }
}
