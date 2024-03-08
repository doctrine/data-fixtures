<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;
use Doctrine\Tests\Mock\ForwardCompatibleEntityManager;
use OutOfBoundsException;

use function sprintf;

class ReferenceRepositoryTest extends BaseTestCase
{
    public function testReferenceEntry(): void
    {
        $em = $this->getMockSqliteEntityManager();

        $role = new TestEntity\Role();
        $role->setName('admin');

        $meta = $em->getClassMetadata(Role::class);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ReferenceRepository($em);
        $this->assertSame($em, $referenceRepo->getManager());

        $referenceRepo->addReference('test', $role);

        $referencesByClass = $referenceRepo->getReferencesByClass();
        $this->assertCount(1, $referencesByClass);
        $this->assertArrayHasKey(Role::class, $referencesByClass);
        $this->assertCount(1, $referencesByClass[Role::class]);
        $this->assertArrayHasKey('test', $referencesByClass[Role::class]);
        $this->assertInstanceOf(Role::class, $referencesByClass[Role::class]['test']);
    }

    public function testReferenceIdentityPopulation(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ReferenceRepository::class)
            ->setConstructorArgs([$em])
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository),
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);

        $referenceRepository->expects($this->once())
            ->method('addReference')
            ->with('admin-role');

        $referenceRepository->expects($this->once())
            ->method('getReferenceNames')
            ->willReturn(['admin-role']);

        $referenceRepository->expects($this->once())
            ->method('setReferenceIdentity')
            ->with('admin-role', ['id' => 1]);

        $roleFixture = new TestFixtures\RoleFixture();
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
    }

    public function testReferenceReconstruction(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository),
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);
        $roleFixture = new TestFixtures\RoleFixture();
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
        // first test against managed state
        $ref = $referenceRepository->getReference('admin-role', Role::class);

        $this->assertNotInstanceOf(Proxy::class, $ref);

        // now test reference reconstruction from identity
        $em->clear();
        $ref = $referenceRepository->getReference('admin-role', Role::class);

        $this->assertInstanceOf(Proxy::class, $ref);
    }

    public function testReferenceMultipleEntries(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);

        $role = new TestEntity\Role();
        $role->setName('admin');

        $em->persist($role);
        $referenceRepository->addReference('admin', $role);
        $referenceRepository->addReference('duplicate', $role);
        $em->flush();
        $em->clear();

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('admin', Role::class));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('duplicate', Role::class));
    }

    public function testUndefinedReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage(sprintf('Reference to "foo" for class "%s" does not exist', Role::class));

        $referenceRepository->getReference('foo', Role::class);
    }

    /** @group legacy */
    public function testLegacyUndefinedReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage(sprintf('Reference to "foo" for class "%s" does not exist', Role::class));

        $referenceRepository->getReference('foo', Role::class);
    }

    public function testThrowsExceptionAddingDuplicatedReference(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);

        $referenceRepository->addReference('duplicated_reference', new Role());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf(
            'Reference to "duplicated_reference" for class "%s" already exists, use method setReference() in order to override it',
            Role::class,
        ));

        $referenceRepository->addReference('duplicated_reference', new Role());
    }

    public function testThereIsNoDuplicateWithDifferentClasses(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);

        $referenceRepository->addReference('not_duplicated_reference', new Role());
        $referenceRepository->addReference('not_duplicated_reference', new User());

        static::assertCount(2, $referenceRepository->getReferencesByClass());
    }

    public function testThrowsExceptionTryingToGetWrongReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage(sprintf('Reference to "missing_reference" for class "%s" does not exist', Role::class));

        $referenceRepository->getReference('missing_reference', Role::class);
    }

    public function testHasIdentityCheck(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->setReferenceIdentity('entity', 1, Role::class);

        $this->assertTrue($referenceRepository->hasIdentity('entity', Role::class));
        $this->assertFalse($referenceRepository->hasIdentity('invalid_entity', Role::class));
        $this->assertEquals(['entity' => 1], $referenceRepository->getIdentitiesByClass()[Role::class] ?? []);
    }

    public function testSetReferenceHavingIdentifier(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);

        $role = new Role();
        $role->setName('role_name');
        $em->persist($role);
        $em->flush();

        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentitiesByClass()[Role::class] ?? [];
        $this->assertCount(1, $identities);
        $this->assertArrayHasKey('entity', $identities);
    }

    public function testGetIdentifierWhenHasNotBeenManagedYetByUnitOfWork(): void
    {
        $role               = new Role();
        $identitiesExpected = ['id' => 1];

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects($this->exactly(2))
            ->method('isInIdentityMap')
            ->with($role)
            ->willReturn(true, false);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->expects($this->once())
            ->method('getIdentifierValues')
            ->with($role)
            ->willReturn($identitiesExpected);
        $classMetadata->expects($this->once())
            ->method('getName')
            ->willReturn(Role::class);

        $em = $this->createMock(ForwardCompatibleEntityManager::class);
        $em->method('getUnitOfWork')
           ->willReturn($uow);
        $em->method('getClassMetadata')
            ->with(Role::class)
            ->willReturn($classMetadata);

        $referenceRepository = new ReferenceRepository($em);
        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentitiesByClass()[Role::class] ?? [];

        $this->assertEquals($identitiesExpected, $identities['entity']);
    }
}
