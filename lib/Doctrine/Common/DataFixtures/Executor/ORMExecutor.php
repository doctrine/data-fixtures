<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
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

    /**
     * Construct new fixtures loader instance.
     *
     * @param EntityManagerInterface $em EntityManagerInterface instance used for persistence.
     */
    public function __construct(EntityManagerInterface $em, ?ORMPurgerInterface $purger = null)
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
    public function execute(array $fixtures, $append = false /* bool $singleTransaction = false */)
    {
        $singleTransaction = func_get_args()[2] ?? true;

        if ($singleTransaction) {
            $executor = $this;
            $this->em->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixtures, $append) {
                if ($append === false) {
                    $this->purge();
                }

                foreach ($fixtures as $fixture) {
                    $this->load($em, $fixture);
                }
            });
        } else {
            if ($append === false) {
                $this->em->beginTransaction();
                $this->purge();
                $this->em->commit();
            }

            foreach ($fixtures as $fixture) {
                $this->em->beginTransaction();
                $this->load($em, $fixture);
                $this->em->commit();
            }
        }
    }
}
