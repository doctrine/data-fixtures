<?php
namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Tests\Common\DataFixtures\BaseTest;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\ExcludedEntity;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\IncludedEntity;

/**
 * 
 *	@author Charles J. C. Elling, Jul 2, 2016
 *
 */
class ORMPurgerExcludeTest extends BaseTest {
	
	const TEST_ENTITY_INCLUDED = 'Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\IncludedEntity';
	const TEST_ENTITY_EXCLUDED = 'Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\ExcludedEntity';
	
	/**
	 * Loads test data
	 * 
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function loadTestData(){
		if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }
        
        $dbParams = array('driver' => 'pdo_sqlite', 'memory' => true);
        $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/../TestPurgeEntity'), true);
        $em = EntityManager::create($dbParams, $config);

        $connection = $em->getConnection();
        $configuration = $connection->getConfiguration();
        $configuration->setFilterSchemaAssetsExpression(null);
        
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema(array(
	            $em->getClassMetadata(self::TEST_ENTITY_INCLUDED),
	            $em->getClassMetadata(self::TEST_ENTITY_EXCLUDED)
        ));
		
        $entity = new ExcludedEntity();
        $entity->setId(1);
        $em->persist($entity);
        
        $entity = new IncludedEntity();
        $entity->setId(1);
        $em->persist($entity);
        
        $em->flush();
		
		return $em;
	}
	
	/**
	 * Execute test purge
	 * 
	 * @param string|null $expression
	 * @param array $list
	 */
	public function executeTestPurge($expression, array $list){
		$em = $this->loadTestData();
		$excludedRepository = $em->getRepository(self::TEST_ENTITY_EXCLUDED);
		$includedRepository = $em->getRepository(self::TEST_ENTITY_INCLUDED);
		
		$excluded = $excludedRepository->findAll();
		$included = $includedRepository->findAll();
		
		$this->assertGreaterThan(0, count($included));
		$this->assertGreaterThan(0, count($excluded));
		
		$connection = $em->getConnection();
		$configuration = $connection->getConfiguration();
		$configuration->setFilterSchemaAssetsExpression($expression);
		
		$purger = new ORMPurger($em,$list);
		$purger->purge();
		
		$excluded = $excludedRepository->findAll();
		$included = $includedRepository->findAll();
		
		$this->assertEquals(0, count($included));
		$this->assertGreaterThan(0, count($excluded));
		
	}
	
	/**
	 * Test for purge exclusion usig dbal filter expression regexp.
	 * 
	 */
	public function testPurgeExcludeUsingFilterExpression(){
		$this->executeTestPurge('~^(?!ExcludedEntity)~', array());
	}
	
	/**
	 * Test for purge exclusion usig explicit exclution list.
	 *
	 */
	public function testPurgeExcludeUsingList(){
		$this->executeTestPurge(null,array('ExcludedEntity'));
	}
}