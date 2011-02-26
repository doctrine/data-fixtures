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

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;

require_once __DIR__.'/TestInit.php';

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
        $this->assertEquals(1, count($references));
        $this->assertArrayHasKey('test', $references);
        $this->assertInstanceOf(self::TEST_ENTITY_ROLE, $references['test']);
    }
    
    private function getMockReferenceRepository()
    {
        return $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->setConstructorArgs(array($this->em))
            ->getMock();
    }
}