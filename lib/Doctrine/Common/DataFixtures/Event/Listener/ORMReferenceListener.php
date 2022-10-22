<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Event\Listener;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use function get_class;

/**
 * Reference Listener populates identities for
 * stored references
 */
final class ORMReferenceListener implements EventSubscriber
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    public function __construct(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        // would be better to use onClear, but it is supported only in 2.1
        return ['postPersist'];
    }

    /**
     * Populates identities for stored references
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();

        $names = $this->referenceRepository->getReferenceNames($object);
        if ($names === false) {
            return;
        }

        foreach ($names as $name) {
            $identity = $args->getEntityManager()
                ->getUnitOfWork()
                ->getEntityIdentifier($object);

            $this->referenceRepository->setReferenceIdentity($name, $identity, get_class($object));
        }
    }
}
