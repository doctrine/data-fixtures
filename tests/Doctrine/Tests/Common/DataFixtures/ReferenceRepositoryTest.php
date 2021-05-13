<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\Exception\UniqueReferencesStockExhaustedException;
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
        $role = $this->createRole('admin', 1, $em);

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
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ReferenceRepository($em);
        $referenceRepo->addUniqueReference('test', $role, 'tag');

        $this->assertTrue($referenceRepo->hasTaggedReference('test', 'tag'));
        $this->assertTrue($referenceRepo->hasTaggedReferences('tag'));

        $references = $referenceRepo->getUniqueReferences();
        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(Role::class, $references['test']);
    }

    public function testTaggedReferenceEntry(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ReferenceRepository($em);
        $referenceRepo->addReference('test', $role, 'tag');

        $this->assertTrue($referenceRepo->hasTaggedReference('test', 'tag'));
        $this->assertTrue($referenceRepo->hasTaggedReferences('tag'));

        $references = $referenceRepo->getReferencesByTag('tag');
        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(Role::class, $references['test']);
    }

    public function testRandomReferenceEntry(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ReferenceRepository($em);
        $referenceRepo->addReference('test', $role, 'tag');

        $this->assertInstanceOf(Role::class, $referenceRepo->getRandomReference('tag'));
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

        $referenceRepository->expects($this->exactly(2))
            ->method('addReference')
            ->withConsecutive(['admin-role'], ['admin-role-tagged']);

        $referenceRepository->expects($this->exactly(2))
            ->method('addUniqueReference')
            ->withConsecutive(
                ['admin-role-unique'],
                ['admin-role-unique-2']
            );

        $referenceRepository->expects($this->exactly(4))
            ->method('getReferenceNames')
            ->will($this->onConsecutiveCalls(
                ['admin-role'],
                ['admin-role-tagged'],
                ['admin-role-unique'],
                ['admin-role-unique-2']
            ));

        $referenceRepository->expects($this->exactly(4))
            ->method('setReferenceIdentity')
            ->withConsecutive(
                ['admin-role', ['id' => 1]],
                ['admin-role-tagged', ['id' => 2]],
                ['admin-role-unique', ['id' => 3]],
                ['admin-role-unique-2', ['id' => 4]]
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

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getRandomReference('tag'));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getRandomReference('tag'));
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
        $this->expectExceptionMessage('There are no unique reference tagged as "invalid-tag".');

        $role = new TestEntity\Role();
        $role->setName('admin');
        $referenceRepository->addUniqueReference('test', $role, 'tag');

        $referenceRepository->getUniqueReference('test', 'invalid-tag');
    }

    public function testUndefinedUniqueReferenceForTagWhenCallingRandomReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('There are no unique reference tagged as "invalid-tag".');

        $referenceRepository->getRandomReference('invalid-tag');
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

    public function testThrowsExceptionTryingToGetWrongUniqueReference(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(OutOfBoundsException::class);
        $referenceRepository->addUniqueReference('reference', new stdClass(), 'tag');
        $this->expectExceptionMessage('Unique reference to "missing_reference" tagged with "tag" does not exist.');

        $referenceRepository->getUniqueReference('missing_reference', 'tag');
    }

    public function testThrowsExceptionTryingToGetRandomReferenceWithWrongTag(): void
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->addUniqueReference('reference', new stdClass(), 'tag');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('There are no unique reference tagged as "invalid-tag".');

        $referenceRepository->getRandomReference('invalid-tag');
    }

    public function testThrowsExceptionTryingToGetObsoleteUniqueReference(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ReferenceRepository($em);

        $referenceRepo->addUniqueReference('test', $role, 'tag');
        $referenceRepo->getUniqueReference('test', 'tag');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Unique reference to "test" has already been used.');

        $referenceRepo->getUniqueReference('test', 'tag');
    }

    public function testThrowsExceptionTryingToGetRandomReferenceWhenStockExhausted(): void
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ReferenceRepository($em);

        $referenceRepo->addUniqueReference('test', $role, 'role');
        $referenceRepo->getRandomReference('role');

        $this->expectException(UniqueReferencesStockExhaustedException::class);
        $this->expectExceptionMessage('The stock of unique references tagged as "role" is exhausted, create more or use less.');

        $referenceRepo->getRandomReference('role');
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

    private function createRole($name, $id, $em)
    {
        $role = new TestEntity\Role();
        $role->setName($name);

        $meta = $em->getClassMetadata(Role::class);
        $meta->getReflectionProperty('id')->setValue($role, $id);

        return $role;
    }
}
