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
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Proxy\Proxy;

/**
 * Test ReferenceRepository.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class ReferenceRepositoryTest extends BaseTest
{
    const TEST_ENTITY_ROLE = 'Doctrine\Tests\Common\DataFixtures\TestEntity\Role';

    public function testReferenceEntry()
    {
        $em = $this->getMockAnnotationReaderEntityManager();
        $role = new TestEntity\Role;
        $role->setName('admin');
        $meta = $em->getClassMetadata(self::TEST_ENTITY_ROLE);
        $meta->getReflectionProperty('id')->setValue($role, 1);

        $referenceRepo = new ReferenceRepository($em);
        $referenceRepo->addReference('test', $role);

        $references = $referenceRepo->getReferences();

        $this->assertCount(1, $references);
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }

    public function testReferenceIdentityPopulation()
    {
        $em = $this->getMockSqliteEntityManager();
        $referenceRepository = $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->setConstructorArgs(array($em))
            ->getMock();
        $em->getEventManager()->addEventSubscriber(
            new ORMReferenceListener($referenceRepository)
        );
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $em->getClassMetadata(self::TEST_ENTITY_ROLE)
        ));

        $referenceRepository->expects($this->once())
            ->method('addReference')
            ->with('admin-role');

        $referenceRepository->expects($this->once())
            ->method('getReferenceName')
            ->will($this->returnValue('admin-role'));

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
            $em->getClassMetadata(self::TEST_ENTITY_ROLE)
        ));
        $roleFixture = new TestFixtures\RoleFixture;
        $roleFixture->setReferenceRepository($referenceRepository);

        $roleFixture->load($em);
        // first test against managed state
        $ref = $referenceRepository->getReference('admin-role');

        $this->assertNotInstanceOf('Doctrine\ORM\Proxy\Proxy', $ref);

        // now test reference reconstruction from identity
        $em->clear();
        $ref = $referenceRepository->getReference('admin-role');

        $this->assertInstanceOf('Doctrine\ORM\Proxy\Proxy', $ref);
    }
}
