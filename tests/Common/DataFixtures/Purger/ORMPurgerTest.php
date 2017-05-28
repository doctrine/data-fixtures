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
    const TEST_ENTITY_USER = 'Doctrine\Tests\Common\DataFixtures\TestEntity\User';
    const TEST_ENTITY_USER_WITH_SCHEMA = 'Doctrine\Tests\Common\DataFixtures\TestEntity\UserWithSchema';
    const TEST_ENTITY_QUOTED = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Quoted';


    public function testGetAssociationTables()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, array(array($metadata), $platform));
        $this->assertEquals($associationTables[0], 'readers.author_reader');
    }

    public function testGetAssociationTablesQuoted()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_QUOTED);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, array(array($metadata), $platform));
        $this->assertEquals($associationTables[0], '"INSERT"');
    }

    public function testTableNameWithSchema()
    {
        $isDoctrine25 = (ORMVersion::compare('2.5.0') <= 0);
        if (!$isDoctrine25) {
            $this->markTestSkipped('@Table schema attribute is not supported in Doctrine < 2.5.0');
        }

        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER_WITH_SCHEMA);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger($em);
        $class = new ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $method = $class->getMethod('getTableName');
        $method->setAccessible(true);
        $tableName = $method->invokeArgs($purger, array($metadata, $platform));
        $this->assertStringStartsWith('test_schema',$tableName);
    }

}
