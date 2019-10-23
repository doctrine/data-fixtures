<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Event\Listener;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

/**
 * Reference Listener populates identities for
 * stored references
 */
final class MongoDBReferenceListener implements EventSubscriber
{
    /** @var ReferenceRepository */
    private $referenceRepository;

    /**
     * Initialize listener
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
        return ['postPersist'];
    }

    /**
     * Populates identities for stored references
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getDocument();

        $names = $this->referenceRepository->getReferenceNames($object);
        if ($names === false) {
            return;
        }

        foreach ($names as $name) {
            $identity = $args->getDocumentManager()
                ->getUnitOfWork()
                ->getDocumentIdentifier($object);

            $this->referenceRepository->setReferenceIdentity($name, $identity);
        }
    }
}
