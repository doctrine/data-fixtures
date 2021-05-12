<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Link;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestTypes\UuidType;
use Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid;

/**
 * Test ProxyReferenceRepository.
 */
class ProxyReferenceRepositoryTest extends BaseTest
{
    public const TEST_ENTITY_ROLE = Role::class;
    public const TEST_ENTITY_LINK = Link::class;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (Type::hasType('uuid')) {
            return;
        }

        Type::addType('uuid', UuidType::class);
    }

    public function testReferenceEntry(): void
    {
        $em   = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ProxyReferenceRepository($em);
        $referenceRepo->addReference('test', $role);

        $references = $referenceRepo->getReferences();

        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }

    public function testTaggedReferenceEntry(): void
    {
        $em   = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ProxyReferenceRepository($em);
        $referenceRepo->addReference('test', $role, 'tag');

        $references = $referenceRepo->getReferencesByTag('tag');

        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }

    public function testUniqueReferenceEntry(): void
    {
        $em   = $this->getMockAnnotationReaderEntityManager();
        $role = $this->createRole('admin', 1, $em);

        $referenceRepo = new ProxyReferenceRepository($em);
        $referenceRepo->addUniqueReference('test', $role, 'role');

        $references = $referenceRepo->getUniqueReferences();

        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }

    public function testReferenceIdentityPopulation(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ProxyReferenceRepository::class)
            ->setConstructorArgs([$em])
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);

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
        $referenceRepository = new ProxyReferenceRepository($em);
        $listener            = new ORMReferenceListener($referenceRepository);
        $em->getEventManager()->addEventSubscriber($listener);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);
        $roleFixture = new TestFixtures\RoleFixture();
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
        // first test against managed state
        $ref = $referenceRepository->getReference('admin-role');

        $this->assertNotInstanceOf(Proxy::class, $ref);

        // test reference reconstruction from serialized data (was managed)
        $serializedData = $referenceRepository->serialize();

        $proxyReferenceRepository = new ProxyReferenceRepository($em);
        $proxyReferenceRepository->unserialize($serializedData);

        $ref = $proxyReferenceRepository->getReference('admin-role');

        // before clearing, the reference is not yet a proxy
        $this->assertNotInstanceOf(Proxy::class, $ref);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $ref);

        // now test reference reconstruction from identity
        $em->clear();
        $ref = $referenceRepository->getReference('admin-role');

        $this->assertInstanceOf(Proxy::class, $ref);

        // test reference reconstruction from serialized data (was identity)
        $serializedData = $referenceRepository->serialize();

        $proxyReferenceRepository = new ProxyReferenceRepository($em);
        $proxyReferenceRepository->unserialize($serializedData);

        $ref = $proxyReferenceRepository->getReference('admin-role');

        $this->assertInstanceOf(Proxy::class, $ref);
    }

    public function testReconstructionOfCustomTypedId(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ProxyReferenceRepository($em);
        $listener            = new ORMReferenceListener($referenceRepository);
        $em->getEventManager()->addEventSubscriber($listener);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_LINK)]);

        $link = new TestEntity\Link(new Uuid('5e48c0d7-78c2-44f5-bed0-e7970b2822b8'));
        $link->setUrl('http://example.com');

        $referenceRepository->addReference('home-link', $link);
        $em->persist($link);
        $em->flush();
        $em->clear();

        $serializedData           = $referenceRepository->serialize();
        $proxyReferenceRepository = new ProxyReferenceRepository($em);
        $proxyReferenceRepository->unserialize($serializedData);

        $this->assertInstanceOf(
            'Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid',
            $proxyReferenceRepository->getReference('home-link')->getId()
        );
    }

    public function testReferenceMultipleEntries(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = new ProxyReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);

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
        $referenceRepository = new ProxyReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);

        $role = new TestEntity\Role();
        $role->setName('admin');

        $em->persist($role);
        $referenceRepository->addUniqueReference('admin', $role, 'role');
        $referenceRepository->addUniqueReference('duplicate', $role, 'role');
        $em->flush();
        $em->clear();

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getRandomReference('role'));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getRandomReference('role'));
    }

    private function createRole($name, $id, $em)
    {
        $role = new TestEntity\Role();
        $role->setName($name);

        $meta = $em->getClassMetadata(self::TEST_ENTITY_ROLE);
        $meta->getReflectionProperty('id')->setValue($role, $id);

        return $role;
    }
}
