<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Event\Listener;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

use function get_class;

/**
 * Reference Listener populates identities for
 * stored references
 */
final class MongoDBReferenceListener implements EventSubscriber
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
        return ['postPersist'];
    }

    /**
     * Populates identities for stored references
     *
     * @return void
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

            $this->referenceRepository->setReferenceIdentity($name, $identity, get_class($object));
        }
    }
}
