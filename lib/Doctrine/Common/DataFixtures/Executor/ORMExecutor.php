<?php

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class ORMExecutor extends AbstractExecutor
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, ORMPurger $purger = null)
    {
        $this->em = $em;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setEntityManager($em);
        }
        parent::__construct($em);
        $this->listener = new ORMReferenceListener($this->referenceRepository);
        $em->getEventManager()->addEventSubscriber($this->listener);
    }

    public function getObjectManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /** @inheritDoc */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->em->getEventManager()->removeEventListener(
            $this->listener->getSubscribedEvents(),
            $this->listener
        );

        $this->referenceRepository = $referenceRepository;
        $this->listener = new ORMReferenceListener($this->referenceRepository);
        $this->em->getEventManager()->addEventSubscriber($this->listener);
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        $executor = $this;
        $this->em->transactional(function(EntityManagerInterface $em) use ($executor, $fixtures, $append) {
            if ($append === false) {
                $executor->purge();
            }
            foreach ($fixtures as $fixture) {
                $executor->load($em, $fixture);
            }
        });
    }
}
