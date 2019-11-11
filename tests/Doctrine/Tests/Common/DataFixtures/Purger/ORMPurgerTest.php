<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use ReflectionClass;

/**
 * Doctrine\Tests\Common\DataFixtures\ORMPurgerTest
 */
class ORMPurgerTest extends BaseTest
{
    public const TEST_ENTITY_USER             = TestEntity\User::class;
    public const TEST_ENTITY_USER_WITH_SCHEMA = TestEntity\UserWithSchema::class;
    public const TEST_ENTITY_QUOTED           = TestEntity\Quoted::class;

    public function testGetAssociationTables()
    {
        $em       = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals($associationTables[0], 'readers.author_reader');
    }

    public function testGetAssociationTablesQuoted()
    {
        $em       = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_QUOTED);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals($associationTables[0], '"INSERT"');
    }

    public function testTableNameWithSchema()
    {
        $em       = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER_WITH_SCHEMA);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, [$metadata, $platform]);
        $this->assertStringStartsWith('test_schema', $tableName);
    }
}
