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

require_once __DIR__.'/TestInit.php';

use Doctrine\Common\DataFixtures\Loader;

/**
 * Test fixtures loader.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class LoaderTest extends BaseTest
{
    public function testLoader()
    {
        $loader = new Loader();
        $loader->addFixture($this->getMock('Doctrine\Common\DataFixtures\FixtureInterface'), array(), array(), 'Mock1');
        $loader->addFixture($this->getMock('Doctrine\Common\DataFixtures\FixtureInterface', array(), array(), 'Mock2'));
        $loader->addFixture($this->getMock('Doctrine\Common\DataFixtures\SharedFixtureInterface', array(), array(), 'Mock3'));

        $this->assertCount(3, $loader->getFixtures());

        $loader->loadFromDirectory(__DIR__.'/TestFixtures');
        $this->assertCount(7, $loader->getFixtures());
        $this->assertTrue($loader->isTransient('TestFixtures\NotAFixture'));
        $this->assertFalse($loader->isTransient('TestFixtures\MyFixture1'));
    }
}
