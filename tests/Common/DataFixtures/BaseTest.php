<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

/**
 * Base test class
 */
abstract class BaseTest extends TestCase
{
    /**
     * EntityManager mock object together with
     * annotation mapping driver
     *
     * @return EntityManager
     */
    protected function getMockAnnotationReaderEntityManager()
    {
        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config   = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true);

        return EntityManager::create($dbParams, $config);
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager()
    {
        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config   = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true);

        return EntityManager::create($dbParams, $config);
    }
}
