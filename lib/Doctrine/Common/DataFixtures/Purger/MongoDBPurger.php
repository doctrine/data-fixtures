<?php

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class MongoDBPurger implements PurgerInterface
{
    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(DocumentManager $dm = null)
    {
        $this->dm = $dm;
    }

    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getObjectManager(): DocumentManager
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function purge()
    {
        $metadatas = $this->dm->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            if ( ! $metadata->isMappedSuperclass) {
                $this->dm->getDocumentCollection($metadata->name)->drop();
            }
        }
        $this->dm->getSchemaManager()->ensureIndexes();
    }
}
