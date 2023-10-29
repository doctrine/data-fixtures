<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use ReflectionClass;

/**
 * Doctrine\Tests\Common\DataFixtures\ORMPurgerTest
 */
class ORMPurgerTest extends BaseTestCase
{
    public const TEST_ENTITY_USER              = TestEntity\User::class;
    public const TEST_ENTITY_USER_WITH_SCHEMA  = TestEntity\UserWithSchema::class;
    public const TEST_ENTITY_QUOTED            = TestEntity\Quoted::class;
    public const TEST_ENTITY_GROUP             = TestEntity\Group::class;
    public const TEST_ENTITY_GROUP_WITH_SCHEMA = TestEntity\GroupWithSchema::class;

    public function testGetAssociationTables(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals('readers.author_reader', $associationTables[0]);
    }

    public function testGetAssociationTablesQuoted(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_QUOTED);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals($associationTables[0], '"INSERT"');
    }

    public function testTableNameWithSchema(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER_WITH_SCHEMA);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, [$metadata, $platform]);
        $this->assertStringStartsWith('test_schema', $tableName);
    }

    public function testGetDeleteFromTableSQL(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_GROUP);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, [$metadata, $platform]);
        $method    = $class->getMethod('getDeleteFromTableSQL');
        $method->setAccessible(true);
        $sql = $method->invokeArgs($purger, [$tableName, $platform]);
        $this->assertEquals('DELETE FROM "Group"', $sql);
    }

    public function testGetDeleteFromTableSQLWithSchema(): void
    {
        $em       = $this->getMockSqliteEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_GROUP_WITH_SCHEMA);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger   = new ORMPurger($em);
        $class    = new ReflectionClass(ORMPurger::class);
        $method   = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, [$metadata, $platform]);
        $method    = $class->getMethod('getDeleteFromTableSQL');
        $method->setAccessible(true);
        $sql = $method->invokeArgs($purger, [$tableName, $platform]);
        $this->assertEquals('DELETE FROM test_schema."group"', $sql);
    }
}
