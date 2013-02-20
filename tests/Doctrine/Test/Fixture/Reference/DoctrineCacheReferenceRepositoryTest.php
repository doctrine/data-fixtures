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

namespace Doctrine\Test\Fixture\Reference;

use Doctrine\Fixture\Reference\DoctrineCacheReferenceRepository;
use Doctrine\Common\Cache\ArrayCache;

/**
 * DoctrineCacheReferenceRepository tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DoctrineCacheReferenceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Reference\DoctrineCacheReferenceRepository
     */
    private $referenceRepository;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->referenceRepository = new DoctrineCacheReferenceRepository(new ArrayCache());
    }

    public function testAdd()
    {
        $this->referenceRepository->add('test', 'foo');

        $this->assertTrue($this->referenceRepository->has('test'));
    }

    public function testHas()
    {
        $this->referenceRepository->add('test', 'foo');

        $this->assertTrue($this->referenceRepository->has('test'));
        $this->assertFalse($this->referenceRepository->has('foo'));
    }

    public function testGet()
    {
        $this->referenceRepository->add('test', 'foo');

        $this->assertEquals('foo', $this->referenceRepository->get('test'));
        $this->assertFalse($this->referenceRepository->get('foo'));
    }

    public function testRemove()
    {
        $this->referenceRepository->add('test', 'foo');

        $this->assertTrue($this->referenceRepository->has('test'));

        $this->referenceRepository->remove('test');

        $this->assertFalse($this->referenceRepository->has('test'));
    }
}