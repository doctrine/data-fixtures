<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Manuel Gonalez <mgonyan@gmail.com>
 */
class ReferenceRepositoryTest extends BaseTest
{
    public function testReferenceEntry()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        
        $role = new TestEntity\Role;
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

    public function testReferenceIdentityPopulation()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder(ReferenceRepository::class)
            ->setConstructorArgs(array($em))
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $em->getClassMetadata(Role::class)
        ));

        $referenceRepository->expects($this->once())
            ->method('addReference')
            ->with('admin-role');

        $referenceRepository->expects($this->once())
            ->method('getReferenceNames')
            ->will($this->returnValue(array('admin-role')));

        $referenceRepository->expects($this->once())
            ->method('setReferenceIdentity')
            ->with('admin-role', array('id' => 1));

        $roleFixture = new TestFixtures\RoleFixture;
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
    }

    public function testReferenceReconstruction()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $em->getClassMetadata(Role::class)
        ));
        $roleFixture = new TestFixtures\RoleFixture;
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

    public function testReferenceMultipleEntries()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);
        $em->getEventManager()->addEventSubscriber(new ORMReferenceListener($referenceRepository));
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema(array($em->getClassMetadata(Role::class)));

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

    public function testUndefinedReference()
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to: (foo) does not exist');

        $referenceRepository->getReference('foo');
    }

    public function testThrowsExceptionAddingDuplicatedReference()
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->addReference('duplicated_reference', new \stdClass());

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Reference to: (duplicated_reference) already exists, use method setReference in order to override it');

        $referenceRepository->addReference('duplicated_reference', new \stdClass());
    }

    public function testThrowsExceptionTryingToGetWrongReference()
    {
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Reference to: (missing_reference) does not exist');

        $referenceRepository->getReference('missing_reference');
    }

    public function testHasIdentityCheck()
    {
        $role = new Role();
        $referenceRepository = new ReferenceRepository($this->getMockSqliteEntityManager());
        $referenceRepository->setReferenceIdentity('entity', $role);

        $this->assertTrue($referenceRepository->hasIdentity('entity'));
        $this->assertFalse($referenceRepository->hasIdentity('invalid_entity'));
        $this->assertEquals(['entity' => $role], $referenceRepository->getIdentities());
    }

    public function testSetReferenceHavingIdentifier()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = new ReferenceRepository($em);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $em->getClassMetadata(Role::class)
        ));

        $role = new Role();
        $role->setName('role_name');
        $em->persist($role);
        $em->flush();

        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentities();
        $this->assertCount(1, $identities);
        $this->assertArrayHasKey('entity', $identities);
    }

    public function testGetIdentifierWhenHasNotBeenManagedYetByUnitOfWork()
    {
        $role = new Role();
        $identitiesExpected = ['id' => 1];

        /** @var UnitOfWork | ProphecyInterface $uow */
        $uow = $this->prophesize(UnitOfWork::class);
        $uow->isInIdentityMap($role)->shouldBeCalledTimes(2)->willReturn(true, false);

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getIdentifierValues($role)->shouldBeCalled()->willReturn($identitiesExpected);

        /** @var EntityManagerInterface | ProphecyInterface $em */
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->getUnitOfWork()->shouldBeCalled()->willReturn($uow);
        $em->getClassMetadata(Role::class)->shouldBeCalled()->willReturn($classMetadata);

        $referenceRepository = new ReferenceRepository($em->reveal());
        $referenceRepository->setReference('entity', $role);
        $identities = $referenceRepository->getIdentities();

        $this->assertEquals($identitiesExpected, $identities['entity']);
    }
}
