<?php


namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use ReflectionClass;

/**
 * Doctrine\Tests\Common\DataFixtures\ORMPurgerTest
 *
 * @author Ivan Molchanov <ivan.molchanov@opensoftdev.ru>
 */
class ORMPurgerTest extends BaseTest
{
    const TEST_ENTITY_USER = 'Doctrine\Tests\Common\DataFixtures\TestEntity\User';
    const TEST_ENTITY_QUOTED = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Quoted';

    public function testGetAssociationTables()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $platform = $em->getConnection()->getDatabasePlatform();
        $purger = new ORMPurger();
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
        $purger = new ORMPurger();
        $class = new ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, array(array($metadata), $platform));
        $this->assertEquals($associationTables[0], '"INSERT"');
    }
}
