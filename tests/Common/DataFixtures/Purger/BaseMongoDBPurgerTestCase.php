<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\Tests\Common\DataFixtures\BaseTestCase;
use MongoCollection;
use MongoDB\Collection;
use MongoDB\Driver\Exception\ConnectionTimeoutException;

use function class_exists;
use function dirname;
use function method_exists;

use const PHP_VERSION_ID;

abstract class BaseMongoDBPurgerTestCase extends BaseTestCase
{
    protected function getDocumentManager(): DocumentManager
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

    protected function getPurger(): MongoDBPurger
    {
        return new MongoDBPurger($this->getDocumentManager());
    }

    protected function assertIndexCount(int $expectedCount, Collection|MongoCollection $collection): void
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
        /** @phpstan-ignore function.alreadyNarrowedType (that method exists only since ODM 2.14.0) */
        if (method_exists($documentManager, 'getClient')) {
            try {
                $documentManager->getClient()->selectDatabase('admin')->command(['ping' => 1]);
            } catch (ConnectionTimeoutException) {
                $this->markTestSkipped('Unable to connect to MongoDB');
            }

            return;
        }

        /** @phpstan-ignore method.notFound (connection methods exist in older ODM versions) */
        if ($documentManager->getConnection()->connect()) {
            return;
        }

        $this->markTestSkipped('Unable to connect to MongoDB');
    }
}
