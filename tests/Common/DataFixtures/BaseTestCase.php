<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     */
    protected function getMockSqliteEntityManager(string $fixtureSet = 'TestEntity'): EntityManager
    {
        $dbParams = ['driver' => 'sqlite3', 'memory' => true];
        $config   = ORMSetup::createAttributeMetadataConfig([__DIR__ . '/' . $fixtureSet], true);
        $config->enableNativeLazyObjects(true);

        $connection = DriverManager::getConnection($dbParams, $config);

        $connection->executeStatement('ATTACH DATABASE \':memory:\' AS readers');

        return new EntityManager($connection, $config);
    }
}
