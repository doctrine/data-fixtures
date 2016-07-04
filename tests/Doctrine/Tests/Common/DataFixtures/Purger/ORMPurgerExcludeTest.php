<?php
namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Tests\Common\DataFixtures\BaseTest;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * 
 *	@author Charles J. C. Elling, Jul 2, 2016
 *
 */
class ORMPurgerExcludeTest extends BaseTest {
	
	const TEST_ENTITY_ROLE = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Role';
	const TEST_ENTITY_USER = 'Doctrine\Tests\Common\DataFixtures\TestEntity\User';
	const TEST_ENTITY_QUOTED = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Quoted';
	
	/**
	 * Loads test data
	 * 
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function loadTestData(){
		if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $em = $this->getMockSqliteEntityManager();
        $connection = $em->getConnection();
        $configuration = $connection->getConfiguration();
        $configuration->setFilterSchemaAssetsExpression(null);
        
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema(array(
	            $em->getClassMetadata(self::TEST_ENTITY_ROLE),
	            $em->getClassMetadata(self::TEST_ENTITY_USER),
        		$em->getClassMetadata(self::TEST_ENTITY_QUOTED)
        ));
		
        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);

        $userFixture = new TestFixtures\UserFixture;
        $roleFixture = new TestFixtures\RoleFixture;
        $executor->execute(array($roleFixture, $userFixture), true);
		
		return $em;
	}
	
	/**
	 * Execute test purge
	 * 
	 * @param EntityManager $em
	 * @param string|null $expression
	 * @param array $list
	 */
	public function executeTestPurge(EntityManager $em, $expression, array $list){
		
		$userRepository = $em->getRepository(self::TEST_ENTITY_USER);
		$roleRepository = $em->getRepository(self::TEST_ENTITY_ROLE);
		
		$users = $userRepository->findAll();
		$roles = $roleRepository->findAll();
		
		$this->assertGreaterThan(0, count($users));
		$this->assertGreaterThan(0, count($roles));

		
		$connection = $em->getConnection();
		$configuration = $connection->getConfiguration();
		$configuration->setFilterSchemaAssetsExpression($expression);
		
		$purger = new ORMPurger($em,$list);
		$purger->purge();
		
		$users = $userRepository->findAll();
		$roles = $roleRepository->findAll();
		
		$this->assertEquals(0, count($users));
		$this->assertGreaterThan(0, count($roles));
		
	}
	
	/**
	 * Test for purge exclusion usig dbal filter expression regexp.
	 * Only purge tables starting whit User
	 * 
	 */
	public function testPurgeExcludeUsingFilterExpression(){
		$em = $this->loadTestData();
		$this->executeTestPurge($em,'~^User~', array());
	}
	
	/**
	 * Test for purge exclusion usig explicit exclution list.
	 * Only purge User table
	 *
	 */
	public function testPurgeExcludeUsingList(){
		$em = $this->loadTestData();
		$connection = $em->getConnection();
		$tables = $connection->getSchemaManager()->listTableNames();
		
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$platform = $em->getConnection()->getDatabasePlatform();
		$purger = new ORMPurger();
		$class = new \ReflectionClass('Doctrine\Common\DataFixtures\Purger\ORMPurger');
		$method = $class->getMethod('getAssociationTables');
		$method->setAccessible(true);
		$associationTables = $method->invokeArgs($purger, array($metadata, $platform));
		
		$excluded = array_diff(array_merge($tables,$associationTables), array('User')); // avoid General error: 1 no such table: readers.author_reader
		$this->executeTestPurge($em,null,$excluded);
	}
}