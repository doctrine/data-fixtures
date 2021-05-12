<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\Exception\UniqueReferencesOutOfStockException;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use OutOfBoundsException;
use Prophecy\Prophecy\ProphecyInterface;
use stdClass;

use function assert;

class ReferenceRepositoryTest extends BaseTest
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

    public function testUniqueReferenceEntry(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();

        $role = new TestEntity\Role();
        $role->setName('admin');

        $meta = $em->getClassMetadata(Role::class);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ReferenceRepository($em);
        $this->assertSame($em, $referenceRepo->getManager());

        $referenceRepo->addUniqueReference('test', $role, 'tag');

        $references = $referenceRepo->getUniqueReferences('tag');
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
            ->method('addUniqueReference')
            ->with('admin-role-unique');

        $referenceRepository->expects($this->exactly(2))
            ->method('getReferenceNames')
            ->will($this->onConsecutiveCalls(['admin-role'], ['admin-role-unique']));

        $referenceRepository->expects($this->exactly(2))
            ->method('setReferenceIdentity')
            ->withConsecutive(
                ['admin-role', ['id' => 1]],
                ['admin-role-unique', ['id' => 2]]
            );

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
        $uniqueRef = $referenceRepository->getUniqueReference('role');

        $this->assertNotInstanceOf(Proxy::class, $ref);
        $this->assertNotInstanceOf(Proxy::class, $uniqueRef);

        // now test reference reconstruction from identity
        // unique references can't perform reconstruction because
        // they are for single use only
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

    public function testUniqueReferenceMultipleEntries(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([$em->getClassMetadata(Role::class)]);

        $role = new TestEntity\Role();
        $role->setName('admin');

        $em->persist($role);
        $referenceRepository->addUniqueReference('admin', $role, 'tag');
        $referenceRepository->addUniqueReference('duplicate', $role, 'tag');
        $em->flush();
        $em->clear();

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getUniqueReference('tag'));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getUniqueReference('tag'));
    }

    public function testUndefinedReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to "foo" does not exist');

        $referenceRepository->getReference('foo');
    }


    public function testUndefinedUniqueReferenceForTag(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('There are no unique references tagged as "invalid-tag".');

        $referenceRepository->getUniqueReference('invalid-tag');
    }

    public function testThrowsExceptionAddingDuplicatedReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->addReference('duplicated_reference', new stdClass());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Reference to "duplicated_reference" already exists, use method setReference in order to override it');

        $referenceRepository->addReference('duplicated_reference', new stdClass());
    }

    public function testThrowsExceptionAddingDuplicatedUniqueReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->addUniqueReference('duplicated_reference', new stdClass(), 'tag');

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Unique reference "duplicated_reference" tagged as "tag" already exists, use method setUniqueReference in order to override it.'
        );

        $referenceRepository->addUniqueReference('duplicated_reference', new stdClass(), 'tag');
    }

    public function testThrowsExceptionTryingToGetWrongReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to "missing_reference" does not exist');

        $referenceRepository->getReference('missing_reference');
    }

    public function testThrowsExceptionTryingToGetWrongUniqueReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('There are no unique references tagged as "tag".');

        $referenceRepository->getUniqueReference('tag');
    }

    public function testThrowsExceptionTryingToGetUniqueReferenceWhenStockExhausted(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();

        $role = new TestEntity\Role();
        $role->setName('admin');

        $meta = $em->getClassMetadata(Role::class);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ReferenceRepository($em);
        $this->assertSame($em, $referenceRepo->getManager());

        $referenceRepo->addUniqueReference('test', $role, 'role');
        $referenceRepo->getUniqueReference('role');

        $this->expectException(UniqueReferencesOutOfStockException::class);
        $this->expectExceptionMessage('The stock of unique references tagged as "role" is exhausted, create more or use less.');

        $referenceRepo->getUniqueReference('role');
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

        $uow = $this->prophesize(UnitOfWork::class);
        assert($uow instanceof UnitOfWork || $uow instanceof ProphecyInterface);
        $uow->isInIdentityMap($role)->shouldBeCalledTimes(2)->willReturn(true, false);

        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getIdentifierValues($role)->shouldBeCalled()->willReturn($identitiesExpected);

        $em = $this->prophesize(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface || $em instanceof ProphecyInterface);
        $em->getUnitOfWork()->shouldBeCalled()->willReturn($uow);
        $em->getClassMetadata(Role::class)->shouldBeCalled()->willReturn($classMetadata);

        $referenceRepository = new ReferenceRepository($em->reveal());
        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentities();

        $this->assertEquals($identitiesExpected, $identities['entity']);
    }
}
