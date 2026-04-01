<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurgeMode;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\Tests\Common\DataFixtures\BaseTestCase;
use Doctrine\Tests\Common\DataFixtures\TestDocument\Role;
use MongoCollection;
use MongoDB\Collection;
use MongoDB\Driver\Exception\ConnectionTimeoutException;

use function class_exists;
use function dirname;
use function method_exists;

use const PHP_VERSION_ID;

class MongoDBPurgerTest extends BaseTestCase
{
    public const TEST_DOCUMENT_ROLE = Role::class;

    private function getDocumentManager(): DocumentManager
    {
        if (! class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Missing doctrine/mongodb-odm');
        }

        $root = dirname(__DIR__, 5);

        $config = new Configuration();
        $config->setProxyDir($root . '/generate/proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($root . '/generate/hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl(AttributeDriver::create(dirname(__DIR__) . '/TestDocument'));

        /** @phpstan-ignore function.alreadyNarrowedType (that method exists only since ODM 2.14.0) */
        if (PHP_VERSION_ID >= 80400 && method_exists($config, 'setUseNativeLazyObject')) {
            $config->setUseNativeLazyObject(true);
        }

        $dm = DocumentManager::create(null, $config);

        $this->skipIfMongoDBUnavailable($dm);

        return $dm;
    }

    private function getPurger(): MongoDBPurger
    {
        return new MongoDBPurger($this->getDocumentManager());
    }

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

    private function assertIndexCount(int $expectedCount, Collection|MongoCollection $collection): void
    {
        if ($collection instanceof Collection) {
            $indexes = $collection->listIndexes();
        } else {
            $indexes = $collection->getIndexInfo();
        }

        $this->assertCount($expectedCount, $indexes);
    }

    private function skipIfMongoDBUnavailable(DocumentManager $documentManager): void
    {
        if (method_exists($documentManager, 'getClient')) {
            try {
                $documentManager->getClient()->selectDatabase('admin')->command(['ping' => 1]);
            } catch (ConnectionTimeoutException) {
                $this->markTestSkipped('Unable to connect to MongoDB');
            }

            return;
        }

        if ($documentManager->getConnection()->connect()) {
            return;
        }

        $this->markTestSkipped('Unable to connect to MongoDB');
    }
}
