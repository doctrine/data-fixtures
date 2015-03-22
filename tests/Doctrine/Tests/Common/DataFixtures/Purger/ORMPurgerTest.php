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

    public function testGetAssociationTables()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $purger = new ORMPurger();
        $class = new ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
        $method = $class->getMethod('getAssociationTables');
        $method->setAccessible(true);
        $associationTables = $method->invokeArgs($purger, array(array($metadata)));
        $method->setAccessible(false);
        $this->assertEquals($associationTables[0], 'readers.author_reader');
    }
}
