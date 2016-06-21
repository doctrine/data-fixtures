<?php


namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Version as ORMVersion;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Account;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Game;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Interest;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\ParentInterest;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Winner;
use ReflectionClass;

/**
 * Doctrine\Tests\Common\DataFixtures\ORMPurgerTest
 *
 * @author Ivan Molchanov <ivan.molchanov@opensoftdev.ru>
 */
class ORMPurgerTest extends BaseTest
{
    const TEST_ENTITY_GAME = 'Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Game';
    const TEST_ENTITY_WINNER = 'Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Winner';
    const TEST_ENTITY_ACCOUNT = 'Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Account';
    const TEST_ENTITY_INTEREST = 'Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Interest';
    const TEST_ENTITY_PARENT_INTEREST = 'Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\ParentInterest';
    const TEST_ENTITY_USER = 'Doctrine\Tests\Common\DataFixtures\TestEntity\User';
    const TEST_ENTITY_USER_WITH_SCHEMA = 'Doctrine\Tests\Common\DataFixtures\TestEntity\UserWithSchema';
    const TEST_ENTITY_QUOTED = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Quoted';

    public function testGetAssociationTables()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_USER);
        $purger = new ORMPurger($em);

        $associationTables = $this->invokePrivateMethod($purger, 'getAssociationTables', array(array($metadata)));
        $this->assertEquals($associationTables[0], 'readers.author_reader');
    }

    public function testGetAssociationTablesQuoted()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $metadata = $em->getClassMetadata(self::TEST_ENTITY_QUOTED);
        $purger = new ORMPurger($em);

        $associationTables = $this->invokePrivateMethod($purger, 'getAssociationTables', array(array($metadata)));
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
        $purger = new ORMPurger($em);

        $tableName = $this->invokePrivateMethod($purger, 'getTableName', array($metadata));
        $this->assertStringStartsWith('test_schema', $tableName);
    }

    public function testCommitOrder()
    {
        $em = $this->getMockSqliteEntityManagerWithDummyEntities();
        $classes = $this->getMetadataClasses($em);
        $purger = new ORMPurger($em);

        $commitOrder = $this->invokePrivateMethod($purger, 'getCommitOrder', array($classes));
        $this->assertEquals(self::TEST_ENTITY_PARENT_INTEREST, $commitOrder[0]->name);
        $this->assertEquals(self::TEST_ENTITY_INTEREST, $commitOrder[1]->name);
        $this->assertEquals(self::TEST_ENTITY_ACCOUNT, $commitOrder[2]->name);
        $this->assertEquals(self::TEST_ENTITY_GAME, $commitOrder[3]->name);
        $this->assertEquals(self::TEST_ENTITY_WINNER, $commitOrder[4]->name);
    }

    public function testPurgeTableCorrectOrder()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $em = $this->getMockSqliteEntityManagerWithDummyEntities();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([
            $em->getClassMetadata(Winner::class),
            $em->getClassMetadata(Account::class),
            $em->getClassMetadata(Interest::class),
            $em->getClassMetadata(Game::class),
            $em->getClassMetadata(ParentInterest::class)
        ]);

        $em->getConnection()->exec('PRAGMA foreign_keys = ON;');

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute([new TestFixtures\PurgeFixture()], true);

        $winnerResult = $em->getConnection()->executeQuery('select * from winner')->fetchAll();
        $this->assertCount(1, $winnerResult);

        $accountResult = $em->getConnection()->executeQuery('select * from account')->fetchAll();
        $this->assertCount(1, $accountResult);

        $gameResult = $em->getConnection()->executeQuery('select * from game')->fetchAll();
        $this->assertCount(1, $gameResult);

        $interestResult = $em->getConnection()->executeQuery('select * from parentinterest')->fetchAll();
        $this->assertCount(1, $interestResult);

        $purger->purge();

        $winnerResult = $em->getConnection()->executeQuery('select * from winner')->fetchAll();
        $this->assertCount(0, $winnerResult);

        $accountResult = $em->getConnection()->executeQuery('select * from account')->fetchAll();
        $this->assertCount(0, $accountResult);

        $gameResult = $em->getConnection()->executeQuery('select * from game')->fetchAll();
        $this->assertCount(0, $gameResult);

        $interestResult = $em->getConnection()->executeQuery('select * from parentinterest')->fetchAll();
        $this->assertCount(0, $interestResult);
    }

    /**
     * @param EntityManagerInterface $em
     * @return array
     */
    private function getMetadataClasses(EntityManagerInterface $em)
    {
        $classes = [];
        foreach ($em->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (!$metadata->isMappedSuperclass && !(isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                $classes[] = $metadata;
            }
        }

        return $classes;
    }

    /**
     * @param $object
     * @param $method
     * @param array $arguments
     * @return mixed
     */
    private function invokePrivateMethod($object, $method, array $arguments)
    {
        $class = new ReflectionClass(get_class($object));
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
