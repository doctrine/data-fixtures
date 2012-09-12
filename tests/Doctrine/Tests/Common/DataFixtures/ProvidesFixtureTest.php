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

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProvidesFixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

require_once __DIR__.'/TestInit.php';

/**
 * Test fixture ordering by provided dependencies.
 *
 * @author Derek J. Lambert <dlambert@dereklambert.com>
 */
class ProvideDependentFixtureTest extends BaseTest
{
    public function testOrderFixturesByProvidesDependencies()
    {
        $loader = new Loader();
        $loader->addFixture(new ProvidesFixture4);
        $loader->addFixture(new ProvidesFixture2);
        $loader->addFixture(new ProvidesFixture3);
        $loader->addFixture(new ProvidesFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertEquals(4, count($orderedFixtures));

        $this->assertTrue(array_shift($orderedFixtures) instanceof ProvidesFixture1);
        $this->assertTrue(array_shift($orderedFixtures) instanceof ProvidesFixture2);
        $this->assertTrue(array_shift($orderedFixtures) instanceof ProvidesFixture3);
        $this->assertTrue(array_shift($orderedFixtures) instanceof ProvidesFixture4);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDuplicateProvidesException()
    {
        $loader = new Loader();
        $loader->addFixture(new ProvidesFixture1);
        $loader->addFixture(new DuplicateProvidesFixture);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadProvidesException()
    {
        $loader = new Loader();
        $loader->addFixture(new ProvidesFixture1);
        $loader->addFixture(new BadProvidesFixture);
    }
}

class ProvidesFixture1 implements FixtureInterface, ProvidesFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvides()
    {
        return 'ProvidesFixture1';
    }
}

class ProvidesFixture2 implements FixtureInterface, DependentFixtureInterface, ProvidesFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array('ProvidesFixture1' );
    }

    public function getProvides()
    {
        return 'ProvidesFixture2';
    }
}

class ProvidesFixture3 implements FixtureInterface, DependentFixtureInterface, ProvidesFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array('ProvidesFixture2' );
    }

    public function getProvides()
    {
        return 'ProvidesFixture3';
    }
}

class ProvidesFixture4 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array( 'ProvidesFixture3' );
    }
}

class DuplicateProvidesFixture implements FixtureInterface, ProvidesFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvides()
    {
        return 'ProvidesFixture1';
    }
}

class BadProvidesFixture implements FixtureInterface, ProvidesFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvides()
    {
        return '';
    }
}
