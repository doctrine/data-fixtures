<?php


namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Tests\Common\DataFixtures\BaseTest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\Tests\Common\DataFixtures\TestDocument\Role;

class MongoDBPurgerTest extends BaseTest
{
    const TEST_DOCUMENT_ROLE = 'Doctrine\Tests\Common\DataFixtures\TestDocument\Role';

    private function getDocumentManager()
    {
        if (!class_exists('Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Missing doctrine/mongodb-odm');
        }

        $root = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

        $config = new Configuration();
        $config->setProxyDir($root . '/generate/proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($root . '/generate/hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl(AnnotationDriver::create(dirname(__DIR__) . '/TestDocument'));
        AnnotationDriver::registerAnnotationClasses();

        $dm = DocumentManager::create(null, $config);
        if (!$dm->getConnection()->connect()) {
            $this->markTestSkipped('Unable to connect to MongoDB');
        }

        return $dm;
    }

    private function getPurger()
    {
        return new MongoDBPurger($this->getDocumentManager());
    }

    public function testPurgeKeepsIndices()
    {
        $purger = $this->getPurger();
        $dm = $purger->getObjectManager();

        $collection = $dm->getDocumentCollection(self::TEST_DOCUMENT_ROLE);
        $collection->drop();

        $this->assertCount(0, $collection->getIndexInfo());

        $role = new Role;
        $role->setName('role');
        $dm->persist($role);
        $dm->flush();

        $schema = $dm->getSchemaManager()->ensureDocumentIndexes(self::TEST_DOCUMENT_ROLE);
        $this->assertCount(2, $collection->getIndexInfo());

        $purger->purge();
        $this->assertCount(2, $collection->getIndexInfo());
    }
}
