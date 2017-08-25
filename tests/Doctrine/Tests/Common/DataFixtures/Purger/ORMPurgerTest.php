<?php


namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Version as ORMVersion;
use ReflectionClass;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Doctrine\Tests\Common\DataFixtures\ORMPurgerTest
 *
 * @author Ivan Molchanov <ivan.molchanov@opensoftdev.ru>
 */
class ORMPurgerTest extends BaseTest
{
    const TEST_ENTITY_USER = TestEntity\User::class;
    const TEST_ENTITY_USER_WITH_SCHEMA = TestEntity\UserWithSchema::class;
    const TEST_ENTITY_QUOTED = TestEntity\Quoted::class;


    public function testGetAssociationTables()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass(ORMPurger::class);
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals($associationTables[0], 'readers.author_reader');
    }

    public function testGetAssociationTablesQuoted()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_QUOTED);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass(ORMPurger::class);
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, [[$metadata], $platform]);
        $this->assertEquals($associationTables[0], '"INSERT"');
    }

    public function testTableNameWithSchema()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER_WITH_SCHEMA);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass(ORMPurger::class);
        $method = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, [$metadata, $platform]);
        $this->assertStringStartsWith('test_schema',$tableName);
    }

}
