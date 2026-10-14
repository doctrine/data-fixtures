<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurgeMode;
use Doctrine\Tests\Common\DataFixtures\TestDocument\Role;

class MongoDBPurgerTest extends BaseMongoDBPurgerTestCase
{
    public const TEST_DOCUMENT_ROLE = Role::class;

    public function testPurgeWithDelete(): void
    {
        $purger = $this->getPurger();
        $dm     = $purger->getObjectManager();
        $purger->setPurgeMode(MongoDBPurgeMode::Delete);

        self::assertSame(MongoDBPurgeMode::Delete, $purger->getPurgeMode());

        $collection = $dm->getDocumentCollection(self::TEST_DOCUMENT_ROLE);
        $collection->drop();

        $this->assertIndexCount(0, $collection);

        $purger->purge();

        // Collection isn't created yet, so no indices should be present
        $this->assertIndexCount(0, $collection);

        $role = new Role();
        $role->setName('role');
        $dm->persist($role);
        $dm->flush();

        // Only the _id_ index should be present after the document is created
        $this->assertIndexCount(1, $collection);

        $purger->purge();

        // After purging, the collection should still exist, but the document should be deleted
        $this->assertIndexCount(1, $collection);
        $this->assertSame(0, $collection->countDocuments());
    }

    public function testPurgeKeepsIndices(): void
    {
        $purger = $this->getPurger();
        $dm     = $purger->getObjectManager();

        $collection = $dm->getDocumentCollection(self::TEST_DOCUMENT_ROLE);
        $collection->drop();

        $this->assertIndexCount(0, $collection);

        $role = new Role();
        $role->setName('role');
        $dm->persist($role);
        $dm->flush();

        $dm->getSchemaManager()->ensureDocumentIndexes(self::TEST_DOCUMENT_ROLE);
        $this->assertIndexCount(2, $collection);

        $purger->purge();
        $this->assertIndexCount(2, $collection);
    }
}
