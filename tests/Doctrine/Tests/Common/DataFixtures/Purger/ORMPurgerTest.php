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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Tests\Common\DataFixtures\BaseTest;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

require_once __DIR__.'/../TestInit.php';

/**
 * Test ORMPurger
 *
 * @author Saem Ghani
 */
class ORMPurgerTest extends BaseTest
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        $this->em = $this->getMockEntityManager();
    }

    /**
     * @test
     */
    public function purgerWhichDefaultsToDeleteMode()
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->expects($this->once())->method('getAllMetadata')->will($this->returnValue(array($this->getRoleTableMetaData())));

        $this->em->getConnection()->expects($this->once())->method('executeUpdate')->with($this->stringStartsWith('DELETE FROM Role'));

        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    /**
     * @test
     */
    public function purgerWhichCanBeSetToTruncateMode()
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->expects($this->once())->method('getAllMetadata')->will($this->returnValue(array($this->getRoleTableMetaData())));

        $platform = $this->getMock('\Doctrine\Tests\DBAL\Mocks\MockPlatform', array('getTruncateTableSQL'));
        $platform->expects($this->once())->method('getTruncateTableSQL')->with($this->equalTo('Role'))->will($this->returnValue('FOO'));
        $this->em->getConnection()
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));
        $this->em->getConnection()->expects($this->once())->method('executeUpdate')->with($this->stringStartsWith('FOO'));

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
    }

    /**
     * @test
     */
    public function theEntityManagerCanBeSetAfterConstruction()
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->expects($this->once())->method('getAllMetadata')->will($this->returnValue(array($this->getRoleTableMetaData())));

        $this->em->getConnection()->expects($this->once())->method('executeUpdate')->with($this->stringStartsWith('DELETE FROM Role'));

        $purger = new ORMPurger();
        $purger->setEntityManager($this->em);
        $purger->purge();
    }

    /**
     * @test
     */
    public function thePurgerWillPurgeAssociatedEntities()
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->expects($this->once())->method('getAllMetadata')->will($this->returnValue(array($this->getUserTableMetaData())));

        $this->em->getConnection()->expects($this->at(1))->method('executeUpdate')->with($this->stringStartsWith('DELETE FROM User'));
        $this->em->getConnection()->expects($this->at(2))->method('executeUpdate')->with($this->stringStartsWith('DELETE FROM Role'));

        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    /**
     * @test
     */
    public function thePurgerCanDoSelectivePurges()
    {
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->expects($this->once())->method('getAllMetadata')->will($this->returnValue(array($this->getUserTableMetaData())));

        $this->em->getConnection()->expects($this->once())->method('executeUpdate')->with($this->stringStartsWith('DELETE FROM User'));

        $purger = new ORMPurger($this->em, array('Role'));
        $purger->purge();
    }

    private function getUserTableMetaData()
    {
        $role = $this->getRoleTableMetaData();
        $this->em->getMetadataFactory()->expects($this->once())->method('getMetaDataFor')->will($this->returnValue($role));
        $user = $this->createMetadata('User');
        $user->associationMappings = array(array(
            'isOwningSide' => true,
            'type' => \Doctrine\ORM\Mapping\ClassMetadata::ONE_TO_MANY,
            'targetEntity' => $role
        ));
        return $user;
    }

    private function getRoleTableMetaData()
    {
        return $this->createMetadata('Role');
    }

    private function createMetadata($name)
    {
        $metadata = $this->getMock('\Doctrine\ORM\Mapping\ClassMetaData', array(), array(), '', false);
        $metadata->name = $name;
        $metadata->isMappedSuperclass = false;
        $metadata->associationMappings = array();
        $metadata->expects($this->once())->method('isInheritanceTypeSingleTable')->will($this->returnValue(false));
        $metadata->expects($this->once())->method('getTableName')->will($this->returnValue($name));

        return $metadata;
    }
}