<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
final class MongoDBPurger implements MongoDBPurgerInterface
{
    /**
     * Construct new purger instance.
     *
     * @param DocumentManager|null $dm DocumentManager instance used for persistence.
     */
    public function __construct(private DocumentManager|null $dm = null)
    {
    }

    /**
     * Set the DocumentManager instance this purger instance should use.
     */
    public function setDocumentManager(DocumentManager $dm): void
    {
        $this->dm = $dm;
    }

    /**
     * Retrieve the DocumentManager instance this purger instance is using.
     */
    public function getObjectManager(): DocumentManager
    {
        return $this->dm;
    }

    public function purge(): void
    {
        $metadatas = $this->dm->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }

            $this->dm->getDocumentCollection($metadata->name)->drop();
        }

        $this->dm->getSchemaManager()->ensureIndexes();
    }
}
