<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
class MongoDBPurger implements PurgerInterface
{
    private ?DocumentManager $dm;

    /**
     * Construct new purger instance.
     *
     * @param DocumentManager|null $dm DocumentManager instance used for persistence.
     */
    public function __construct(?DocumentManager $dm = null)
    {
        $this->dm = $dm;
    }

    /**
     * Set the DocumentManager instance this purger instance should use.
     *
     * @return void
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Retrieve the DocumentManager instance this purger instance is using.
     *
     * @return DocumentManager
     */
    public function getObjectManager()
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function purge()
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
