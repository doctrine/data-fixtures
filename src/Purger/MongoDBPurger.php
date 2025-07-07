<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\MongoDB\DocumentManager;
use LogicException;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
final class MongoDBPurger implements MongoDBPurgerInterface
{
    /**
     * Purge the collections using deleteMany(). Don't create them.
     */
    public const PURGE_MODE_DELETE = 1;

    /**
     * Drop the collections when purging, then recreate them.
     */
    public const PURGE_MODE_DROP = 2;

    /** @var self::PURGE_MODE_* $mode */
    private int $purgeMode = self::PURGE_MODE_DELETE;

    /**
     * Construct new purger instance.
     *
     * @param DocumentManager|null $dm DocumentManager instance used for persistence.
     */
    public function __construct(private DocumentManager|null $dm = null)
    {
    }

    /**
     * If the purge should be done through collection drop() or deleteMany() statements
     *
     * @param self::PURGE_MODE_* $mode
     */
    public function setPurgeMode(int $mode): void
    {
        $this->purgeMode = $mode;
    }

    /**
     * Get the purge mode
     *
     * @return self::PURGE_MODE_*
     */
    public function getPurgeMode(): int
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
            self::PURGE_MODE_DELETE => $this->purgeWithDelete(),
            self::PURGE_MODE_DROP => $this->purgeWithDrop(),
            default => throw new LogicException('Invalid purge mode specified.'),
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
        if (method_exists($schemaManager, 'createSearchIndexes')) {
            $schemaManager->createSearchIndexes();
        }
    }
}
