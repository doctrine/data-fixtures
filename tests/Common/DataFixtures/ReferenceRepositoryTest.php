<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use OutOfBoundsException;

class ReferenceRepositoryTest extends BaseTestCase
{
    public function testReferenceEntry(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();

        $role = new TestEntity\Role();
        $role->setName('admin');

        $meta = $em->getClassMetadata(Role::class);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ReferenceRepository($em);
        $this->assertSame($em, $referenceRepo->getManager());

        $referenceRepo->addReference('test', $role);

        $references = $referenceRepo->getReferences();
        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(Role::class, $references['test']);
    }

    public function testReferenceIdentityPopulation(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ReferenceRepository::class)
            ->setConstructorArgs([$em])
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);

        $referenceRepository->expects($this->once())
            ->method('addReference')
            ->with('admin-role');

        $referenceRepository->expects($this->once())
            ->method('getReferenceNames')
            ->will($this->returnValue(['admin-role']));

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
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);
        $roleFixture = new TestFixtures\RoleFixture();
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
        // first test against managed state
        $ref = $referenceRepository->getReference('admin-role');

        $this->assertNotInstanceOf(Proxy::class, $ref);

        // now test reference reconstruction from identity
        $em->clear();
        $ref = $referenceRepository->getReference('admin-role');

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

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('admin'));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('duplicate'));
    }

    public function testUndefinedReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to "foo" does not exist');

        $referenceRepository->getReference('foo');
    }

    public function testThrowsExceptionAddingDuplicatedReference(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);

        $referenceRepository->addReference('duplicated_reference', new Role());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Reference to "duplicated_reference" already exists, use method setReference() in order to override it'
        );

        $referenceRepository->addReference('duplicated_reference', new Role());
    }

    public function testThrowsExceptionTryingToGetWrongReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to "missing_reference" does not exist');

        $referenceRepository->getReference('missing_reference');
    }

    public function testHasIdentityCheck(): void
    {
        $role                = new Role();
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->setReferenceIdentity('entity', $role);

        $this->assertTrue($referenceRepository->hasIdentity('entity'));
        $this->assertFalse($referenceRepository->hasIdentity('invalid_entity'));
        $this->assertEquals(['entity' => $role], $referenceRepository->getIdentities());
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
        $identities = $referenceRepository->getIdentities();
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

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getUnitOfWork')
           ->willReturn($uow);
        $em->method('getClassMetadata')
            ->with(Role::class)
            ->willReturn($classMetadata);

        $referenceRepository = new ReferenceRepository($em);
        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentities();

        $this->assertEquals($identitiesExpected, $identities['entity']);
    }
}
