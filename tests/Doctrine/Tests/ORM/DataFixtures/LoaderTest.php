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

namespace Doctrine\Tests\ORM\DataFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\DataFixtures\Loader;
use Doctrine\ORM\DataFixtures\Fixture;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../../lib/Doctrine/ORM/DataFixtures/Fixture.php';
require_once __DIR__ . '/../../../../../lib/Doctrine/ORM/DataFixtures/Loader.php';
require_once __DIR__ . '/Mocks/EntityManager.php';

/**
 * Test fixtures loader.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class LoaderTest extends PHPUnit_Framework_TestCase
{
    public function testLoader()
    {
        $em = new \Doctrine\ORM\EntityManager();
        $loader = new Loader($em);
        $fixture = new MyFixture1;
        $loader->addFixture($fixture);

        $this->assertEquals(1, count($loader->getFixtures()));

        $loader->loadFromDirectory(__DIR__.'/TestFixtures');

        $this->assertEquals(3, count($loader->getFixtures()));

        $loader->execute();

        $fixtures = $loader->getFixtures();
        $this->assertTrue($fixtures[0]->loaded);
        $this->assertTrue($fixtures[1]->loaded);
        $this->assertTrue($fixtures[2]->loaded);

        $this->assertFalse($loader->isTransient(__NAMESPACE__ . '\MyFixture1'));
        $this->assertTrue($loader->isTransient(__NAMESPACE__ . '\NotAFixtureClass'));
    }
}

class MyFixture1 implements Fixture
{
    public $loaded = false;

    public function load(EntityManager $em)
    {
        $this->loaded = true;
    }
}

class NotAFixtureClass {}