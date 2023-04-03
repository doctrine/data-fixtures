<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

/**
 * Base test class
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * EntityManager mock object together with
     * annotation mapping driver
     */
    protected function getMockAnnotationReaderEntityManager(): EntityManager
    {
        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config   = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true);

        return new EntityManager(DriverManager::getConnection($dbParams, $config), $config);
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     */
    protected function getMockSqliteEntityManager(): EntityManager
    {
        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config   = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true);

        return new EntityManager(DriverManager::getConnection($dbParams, $config), $config);
    }
}
