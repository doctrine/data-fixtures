<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\MongoDB\DocumentManager;

use function method_exists;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
final class MongoDBPurger implements MongoDBPurgerInterface
{
    private MongoDBPurgeMode $purgeMode = MongoDBPurgeMode::Drop;

    /**
     * Construct new purger instance.
     *
     * @param DocumentManager|null $dm DocumentManager instance used for persistence.
     */
    public function __construct(private DocumentManager|null $dm = null)
    {
    }

    /**
     * If the purge should be done through collection drop() or deleteMany()
     */
    public function setPurgeMode(MongoDBPurgeMode $mode): void
    {
        $this->purgeMode = $mode;
    }

    public function getPurgeMode(): MongoDBPurgeMode
    {
        return $this->purgeMode;
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
        match ($this->purgeMode) {
            MongoDBPurgeMode::Delete => $this->purgeWithDelete(),
            MongoDBPurgeMode::Drop => $this->purgeWithDrop(),
        };
    }

    private function purgeWithDelete(): void
    {
        $allMetadata = $this->dm->getMetadataFactory()->getAllMetadata();
        foreach ($allMetadata as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }

            $this->dm->getDocumentCollection($metadata->name)->deleteMany([]);
        }
    }

    private function purgeWithDrop(): void
    {
        $allMetadata = $this->dm->getMetadataFactory()->getAllMetadata();
        foreach ($allMetadata as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }

            $this->dm->getDocumentCollection($metadata->name)->drop();
        }

        $schemaManager = $this->dm->getSchemaManager();
        $schemaManager->createCollections();
        $schemaManager->ensureIndexes();

        // Requires doctrine/mongodb-odm 2.8
        // @phpstan-ignore function.alreadyNarrowedType
        method_exists($schemaManager, 'createSearchIndexes') && $schemaManager->createSearchIndexes();
    }
}
