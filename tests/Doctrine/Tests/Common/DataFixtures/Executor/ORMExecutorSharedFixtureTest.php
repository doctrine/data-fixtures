<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;

use function extension_loaded;

/**
 * Test referenced fixture execution
 */
class ORMExecutorSharedFixtureTest extends BaseTest
{
    public const TEST_ENTITY_ROLE = Role::class;
    public const TEST_ENTITY_USER = User::class;

    public function testFixtureExecution(): void
    {
        $em       = $this->getMockAnnotationReaderEntityManager();
        $purger   = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);

        $referenceRepository = $executor->getReferenceRepository();
        $fixture             = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);

        $fixture->expects($this->once())
            ->method('setReferenceRepository')
            ->with($referenceRepository);

        $executor->execute([$fixture], true);
    }

    public function testSharedFixtures(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Missing pdo_sqlite extension.');
        }

        $em         = $this->getMockSqliteEntityManager();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([
            $em->getClassMetadata(self::TEST_ENTITY_ROLE),
            $em->getClassMetadata(self::TEST_ENTITY_USER),
        ]);

        $purger   = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);

        $userFixture           = new TestFixtures\UserFixture();
        $roleFixture           = new TestFixtures\RoleFixture();
        $uniqueRoleUserFixture = new TestFixtures\UniqueRoleUserFixture();
        $executor->execute([$roleFixture, $userFixture, $uniqueRoleUserFixture], true);

        $referenceRepository = $executor->getReferenceRepository();
        $references          = $referenceRepository->getReferences();
        $uniqueReferences    = $referenceRepository->allUniqueReferences();

        $this->assertCount(3, $references);
        $this->assertCount(2, $uniqueReferences);

        $roleReference = $referenceRepository->getReference('admin-role');
        $uniqueRoleReference = $referenceRepository->getReference('admin-role-unique');

        $this->assertInstanceOf(Role::class, $roleReference);
        $this->assertInstanceOf(Role::class, $uniqueRoleReference);

        $this->assertEquals('admin', $roleReference->getName());
        $this->assertEquals('admin-unique', $uniqueRoleReference->getName());

        $userReference = $referenceRepository->getReference('admin');
        $this->assertInstanceOf(User::class, $userReference);
        $this->assertEquals('admin@example.com', $userReference->getEmail());

        $userReference = $referenceRepository->getUniqueReference('user');
        $this->assertInstanceOf(User::class, $userReference);
        $this->assertEquals('admin-unique-role@example.com', $userReference->getEmail());
    }

    private function getMockFixture(): SharedFixtureInterface
    {
        return $this->createMock(SharedFixtureInterface::class);
    }
}
