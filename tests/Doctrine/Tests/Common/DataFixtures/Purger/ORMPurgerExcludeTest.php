<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\ExcludedEntity;
use Doctrine\Tests\Common\DataFixtures\TestPurgeEntity\IncludedEntity;

use function count;
use function extension_loaded;
use function method_exists;
use function preg_match;

class ORMPurgerExcludeTest extends BaseTest
{
    public const TEST_ENTITY_INCLUDED = IncludedEntity::class;
    public const TEST_ENTITY_EXCLUDED = ExcludedEntity::class;

    /**
     * Loads test data
     *
     * @return EntityManager
     */
    protected function loadTestData()
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];
        $config   = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . '/../TestPurgeEntity'], true);
        $em       = EntityManager::create($dbParams, $config);

        $connection    = $em->getConnection();
        $configuration = $connection->getConfiguration();
        if (method_exists($configuration, 'setFilterSchemaAssetsExpression')) {
            $configuration->setFilterSchemaAssetsExpression(null);
        }

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
     * @param string|null $expression
     * @param string[]    $list
     */
    public function executeTestPurge($expression, array $list, ?callable $filter = null): void
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
        if ($expression !== null) {
            if (! method_exists($configuration, 'setFilterSchemaAssetsExpression')) {
                $this->markTestSkipped('DBAL 2 is required to test schema assets filters');
            }

            $configuration->setFilterSchemaAssetsExpression($expression);
        }

        if ($filter !== null) {
            if (! method_exists($configuration, 'setSchemaAssetsFilter')) {
                $this->markTestSkipped('DBAL 2.9 or newer is required to test schema assets filters');
            }

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
     * Test for purge exclusion usig dbal filter expression regexp.
     */
    public function testPurgeExcludeUsingFilterExpression(): void
    {
        $this->executeTestPurge('~^(?!ExcludedEntity)~', [], null);
    }

    /**
     * Test for purge exclusion usig explicit exclution list.
     */
    public function testPurgeExcludeUsingList(): void
    {
        $this->executeTestPurge(null, ['ExcludedEntity'], null);
    }

    public function testPurgeExcludeUsingFilterCallable(): void
    {
        $this->executeTestPurge(null, [], static function (string $table): bool {
            return (bool) preg_match('~^(?!ExcludedEntity)~', $table);
        });
    }
}
