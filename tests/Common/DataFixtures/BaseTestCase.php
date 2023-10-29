<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

use function method_exists;

use const PHP_VERSION_ID;

/**
 * Base test class
 */
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
        if (PHP_VERSION_ID >= 80100) {
            $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . '/' . $fixtureSet], true);
            $config->setLazyGhostObjectEnabled(true);
        } else {
            $config = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/' . $fixtureSet], true);
        }

        $connection = DriverManager::getConnection($dbParams, $config);
        $platform   = $connection->getDatabasePlatform();
        if (method_exists($platform, 'disableSchemaEmulation')) {
            $platform->disableSchemaEmulation();
        }

        $connection->executeStatement('ATTACH DATABASE \':memory:\' AS readers');

        return new EntityManager($connection, $config);
    }
}
