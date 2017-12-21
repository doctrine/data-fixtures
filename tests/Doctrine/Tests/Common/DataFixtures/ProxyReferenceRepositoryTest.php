<?php

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;

/**
 * Test ProxyReferenceRepository.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class ProxyReferenceRepositoryTest extends BaseTest
{
    const TEST_ENTITY_ROLE = Role::class;

    public function testReferenceEntry()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = new TestEntity\Role;
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
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ProxyReferenceRepository::class)
            ->setConstructorArgs([$em])
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([
            $em->getClassMetadata(self::TEST_ENTITY_ROLE)
        ]);

        $referenceRepository->expects($this->once())
            ->method('addReference')
            ->with('admin-role');

        $referenceRepository->expects($this->once())
            ->method('getReferenceNames')
            ->will($this->returnValue(['admin-role']));

        $referenceRepository->expects($this->once())
            ->method('setReferenceIdentity')
            ->with('admin-role', ['id' => 1]);

        $roleFixture = new TestFixtures\RoleFixture;
        $roleFixture->setReferenceRepository($referenceRepository);
        $roleFixture->load($em);
    }

    public function testReferenceReconstruction()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = new ProxyReferenceRepository($em);
        $listener = new ORMReferenceListener($referenceRepository);
        $em->getEventManager()->addEventSubscriber($listener);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema([
            $em->getClassMetadata(self::TEST_ENTITY_ROLE)
        ]);
        $roleFixture = new TestFixtures\RoleFixture;
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
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = new ProxyReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([$em->getClassMetadata(self::TEST_ENTITY_ROLE)]);

        $role = new TestEntity\Role;
        $role->setName('admin');

        $em->persist($role);
        $referenceRepository->addReference('admin', $role);
        $referenceRepository->addReference('duplicate', $role);
        $em->flush();
        $em->clear();

        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('admin'));
        $this->assertInstanceOf(Proxy::class, $referenceRepository->getReference('duplicate'));
    }
}
