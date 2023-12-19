<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Link;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestTypes\UuidType;
use Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid;

/**
 * Test ProxyReferenceRepository.
 */
class ProxyReferenceRepositoryTest extends BaseTestCase
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
        $em   = $this->getMockSqliteEntityManager();
        $role = new TestEntity\Role();
        $role->setName('admin');
        $meta = $em->getClassMetadata(self::TEST_ENTITY_ROLE);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ProxyReferenceRepository($em);
        $referenceRepo->addReference('test', $role);

        $referencesByClass = $referenceRepo->getReferencesByClass();

        $this->assertCount(1, $referencesByClass);
        $this->assertArrayHasKey(Role::class, $referencesByClass);
        $this->assertCount(1, $referencesByClass[Role::class]);
        $this->assertArrayHasKey('test', $referencesByClass[Role::class]);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $referencesByClass[Role::class]['test']);
    }

    public function testReferenceIdentityPopulation(): void
    {
        $em                  = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ProxyReferenceRepository::class)
            ->setConstructorArgs([$em])
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository),
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);

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
        $ref = $referenceRepository->getReference('admin-role', Role::class);

        $this->assertNotInstanceOf(Proxy::class, $ref);

        // test reference reconstruction from serialized data (was managed)
        $serializedData = $referenceRepository->serialize();

        $proxyReferenceRepository = new ProxyReferenceRepository($em);
        $proxyReferenceRepository->unserialize($serializedData);

        $ref = $proxyReferenceRepository->getReference('admin-role', Role::class);

        // before clearing, the reference is not yet a proxy
        $this->assertNotInstanceOf(Proxy::class, $ref);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $ref);

        // now test reference reconstruction from identity
        $em->clear();
        $ref = $referenceRepository->getReference('admin-role', Role::class);

        $this->assertInstanceOf(Proxy::class, $ref);

        // test reference reconstruction from serialized data (was identity)
        $serializedData = $referenceRepository->serialize();

        $proxyReferenceRepository = new ProxyReferenceRepository($em);
        $proxyReferenceRepository->unserialize($serializedData);

        $ref = $proxyReferenceRepository->getReference('admin-role', Role::class);

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
            $proxyReferenceRepository->getReference('home-link', Link::class)->getId(),
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

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('admin', Role::class));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('duplicate', Role::class));
    }
}
