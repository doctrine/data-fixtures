<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\ExcludedEntity;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\IncludedEntity;

use function count;
use function preg_match;

class ORMPurgerExcludeTest extends BaseTestCase
{
    public const TEST_ENTITY_INCLUDED = IncludedEntity::class;
    public const TEST_ENTITY_EXCLUDED = ExcludedEntity::class;

    /**
     * Loads test data
     */
    protected function loadTestData(): EntityManager
    {
        $em = $this->getMockSqliteEntityManager('TestPurgeEntity');

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema([
            $em->getClassMetadata(self::TEST_ENTITY_INCLUDED),
            $em->getClassMetadata(self::TEST_ENTITY_EXCLUDED),
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
     * @param string[] $list
     */
    private function executeTestPurge(array $list, callable|null $filter): void
    {
        $em                 = $this->loadTestData();
        $excludedRepository = $em->getRepository(self::TEST_ENTITY_EXCLUDED);
        $includedRepository = $em->getRepository(self::TEST_ENTITY_INCLUDED);

        $excluded = $excludedRepository->findAll();
        $included = $includedRepository->findAll();

        $this->assertGreaterThan(0, count($included));
        $this->assertGreaterThan(0, count($excluded));

        $connection    = $em->getConnection();
        $configuration = $connection->getConfiguration();

        if ($filter !== null) {
            $configuration->setSchemaAssetsFilter($filter);
        }

        $purger = new ORMPurger($em, $list);
        $purger->purge();

        $excluded = $excludedRepository->findAll();
        $included = $includedRepository->findAll();

        $this->assertCount(0, $included);
        $this->assertGreaterThan(0, count($excluded));
    }

    /**
     * Test for purge exclusion usig explicit exclution list.
     */
    public function testPurgeExcludeUsingList(): void
    {
        $this->executeTestPurge(['ExcludedEntity'], null);
    }

    public function testPurgeExcludeUsingFilterCallable(): void
    {
        $this->executeTestPurge(
            [],
            static fn (string $table): bool => (bool) preg_match('~^(?!ExcludedEntity)~', $table),
        );
    }
}
