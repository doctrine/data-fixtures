<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;
use Doctrine\Tests\Common\DataFixtures\TestEntity\UserRole;

/**
 * Test ProxyReferenceRepository.
 */
class ProxyReferenceRepositoryTest extends BaseTest
{
    public const TEST_ENTITY_ROLE = Role::class;
    public const TEST_ENTITY_USER = User::class;
    public const TEST_ENTITY_USER_ROLE = UserRole::class;

    public function testReferenceEntry()
    {
        $em   = $this->getMockAnnotationReaderEntityManager();
        $role = new TestEntity\Role();
        $role->setName('admin');
        $meta = $em->getClassMetadata(self::TEST_ENTITY_ROLE);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ProxyReferenceRepository($em);
        $referenceRepo->addReference('test', $role);

        $references = $referenceRepo->getReferences();

        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }

    public function testReferenceIdentityPopulation()
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

    public function testReferenceReconstruction()
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

    public function testReferenceMultipleEntries()
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

    public function testCompositeForeignKeysReconstruction()
    {
        $em = $this->getMockSqliteEntityManager();
        $this->prepareSchema(
            $em,
            array(
                self::TEST_ENTITY_ROLE,
                self::TEST_ENTITY_USER,
                self::TEST_ENTITY_USER_ROLE
            )
        );

        $referenceRepository = new ProxyReferenceRepository($em);

        $roleFixture = new TestFixtures\RoleFixture;
        $roleFixture->setReferenceRepository($referenceRepository);
        $roleFixture->load($em);

        $userFixture = new TestFixtures\UserFixture;
        $userFixture->setReferenceRepository($referenceRepository);
        $userFixture->load($em);

        $userFixture = new TestFixtures\UserRoleFixture;
        $userFixture->setReferenceRepository($referenceRepository);
        $userFixture->load($em);

        $em->clear();

        $data = $referenceRepository->serialize();

        $referenceRepository = new ProxyReferenceRepository($em);
        $referenceRepository->unserialize($data);

        $compositeKey = $referenceRepository->getReference('composite-key');
        $this->assertInstanceOf('Doctrine\ORM\Proxy\Proxy', $compositeKey);
    }
}
