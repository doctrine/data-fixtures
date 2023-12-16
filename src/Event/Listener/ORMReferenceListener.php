<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Event\Listener;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostPersistEventArgs;

/**
 * Reference Listener populates identities for
 * stored references
 */
final class ORMReferenceListener implements EventSubscriber
{
    public function __construct(private ReferenceRepository $referenceRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        // would be better to use onClear, but it is supported only in 2.1
        return ['postPersist'];
    }

    /**
     * Populates identities for stored references
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();

        $names = $this->referenceRepository->getReferenceNames($object);
        if ($names === false) {
            return;
        }

        foreach ($names as $name) {
            $identity = $args->getObjectManager()
                ->getUnitOfWork()
                ->getEntityIdentifier($object);

            $this->referenceRepository->setReferenceIdentity($name, $identity, $object::class);
        }
    }
}
