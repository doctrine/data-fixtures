<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Tests\Common\DataFixtures\BaseTest;
use Doctrine\Tests\Common\DataFixtures\TestDocument\Role;
use MongoDB\Collection;
use function class_exists;
use function dirname;

class MongoDBPurgerTest extends BaseTest
{
    public const TEST_DOCUMENT_ROLE = Role::class;

    private function getDocumentManager()
    {
        if (! class_exists('Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Missing doctrine/mongodb-odm');
        }

        $root = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

        $config = new Configuration();
        $config->setProxyDir($root . '/generate/proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($root . '/generate/hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl(AnnotationDriver::create(dirname(__DIR__) . '/TestDocument'));

        AnnotationRegistry::registerLoader('class_exists');

        return DocumentManager::create(null, $config);
    }

    private function getPurger()
    {
        return new MongoDBPurger($this->getDocumentManager());
    }

    public function testPurgeKeepsIndices()
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

    private function assertIndexCount(int $expectedCount, $collection)
    {
        if ($collection instanceof Collection) {
            $indexes = $collection->listIndexes();
        } else {
            $indexes = $collection->getIndexInfo();
        }

        $this->assertCount($expectedCount, $indexes);
    }
}
