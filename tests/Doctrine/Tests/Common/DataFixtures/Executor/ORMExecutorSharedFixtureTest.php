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

    public function testFixtureExecution()
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

    public function testSharedFixtures()
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

        $userFixture = new TestFixtures\UserFixture();
        $roleFixture = new TestFixtures\RoleFixture();
        $executor->execute([$roleFixture, $userFixture], true);

        $referenceRepository = $executor->getReferenceRepository();
        $references          = $referenceRepository->getReferences();

        $this->assertCount(2, $references);
        $roleReference = $referenceRepository->getReference('admin-role');
        $this->assertInstanceOf(Role::class, $roleReference);
        $this->assertEquals('admin', $roleReference->getName());

        $userReference = $referenceRepository->getReference('admin');
        $this->assertInstanceOf(User::class, $userReference);
        $this->assertEquals('admin@example.com', $userReference->getEmail());
    }

    private function getMockFixture()
    {
        return $this->createMock(SharedFixtureInterface::class);
    }
}
