<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
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
        $config   = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true, null, null, false);

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
        $config   = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/TestEntity'], true, null, null, false);

        return EntityManager::create($dbParams, $config);
    }

    /**
     * Prepare the database schema
     * 
     * @param EntityManager $em
     * @param array         $entities
     */
    protected function prepareSchema(EntityManager $em, array $entities)
    {
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(
            array_map(
                function ($entity) use ($em) {
                    return $em->getClassMetadata($entity);
                },
                $entities
            )
        );
    }
}
