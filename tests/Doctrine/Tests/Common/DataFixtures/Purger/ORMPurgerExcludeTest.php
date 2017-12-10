<?php
namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Tests\Common\DataFixtures\BaseTest;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\ExcludedEntity;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\IncludedEntity;

class ORMPurgerExcludeTest extends BaseTest
{
    const TEST_ENTITY_INCLUDED = IncludedEntity::class;
    const TEST_ENTITY_EXCLUDED = ExcludedEntity::class;

    /**
     * Loads test data
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function loadTestData(){
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/../TestPurgeEntity'], true);
        $em = EntityManager::create($dbParams, $config);

        $connection = $em->getConnection();
        $configuration = $connection->getConfiguration();
        $configuration->setFilterSchemaAssetsExpression(null);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema([
            $em->getClassMetadata(self::TEST_ENTITY_INCLUDED),
            $em->getClassMetadata(self::TEST_ENTITY_EXCLUDED)
        ]);

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

        $this->assertCount(0, $included);
        $this->assertGreaterThan(0, count($excluded));
    }

    /**
     * Test for purge exclusion usig dbal filter expression regexp.
     */
    public function testPurgeExcludeUsingFilterExpression(){
        $this->executeTestPurge('~^(?!ExcludedEntity)~', []);
    }

    /**
     * Test for purge exclusion usig explicit exclution list.
     */
    public function testPurgeExcludeUsingList(){
        $this->executeTestPurge(null, ['ExcludedEntity']);
    }
}
