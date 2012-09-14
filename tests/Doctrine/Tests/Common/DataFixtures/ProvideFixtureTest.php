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
use Doctrine\Common\DataFixtures\ProvideFixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

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
        $loader->addFixture(new ProvideFixture4);
        $loader->addFixture(new ProvideFixture2);
        $loader->addFixture(new ProvideFixture3);
        $loader->addFixture(new ProvideFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(4, $orderedFixtures);

        $this->assertInstanceOf('Doctrine\Tests\Common\DataFixtures\ProvideFixture1', array_shift($orderedFixtures));
        $this->assertInstanceOf('Doctrine\Tests\Common\DataFixtures\ProvideFixture2', array_shift($orderedFixtures));
        $this->assertInstanceOf('Doctrine\Tests\Common\DataFixtures\ProvideFixture3', array_shift($orderedFixtures));
        $this->assertInstanceOf('Doctrine\Tests\Common\DataFixtures\ProvideFixture4', array_shift($orderedFixtures));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDuplicateProvidesException()
    {
        $loader = new Loader();
        $loader->addFixture(new ProvideFixture1);
        $loader->addFixture(new DuplicateProvideFixture);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadProvidesException()
    {
        $loader = new Loader();
        $loader->addFixture(new ProvideFixture1);
        $loader->addFixture(new BadProvideFixture);
    }
}

class ProvideFixture1 implements FixtureInterface, ProvideFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvide()
    {
        return 'ProvideFixture1';
    }
}

class ProvideFixture2 implements FixtureInterface, DependentFixtureInterface, ProvideFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array('ProvideFixture1' );
    }

    public function getProvide()
    {
        return 'ProvideFixture2';
    }
}

class ProvideFixture3 implements FixtureInterface, DependentFixtureInterface, ProvideFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array('ProvideFixture2' );
    }

    public function getProvide()
    {
        return 'ProvideFixture3';
    }
}

class ProvideFixture4 implements FixtureInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getDependencies()
    {
        return array( 'ProvideFixture3' );
    }
}

class DuplicateProvideFixture implements FixtureInterface, ProvideFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvide()
    {
        return 'ProvideFixture1';
    }
}

class BadProvideFixture implements FixtureInterface, ProvideFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getProvide()
    {
        return '';
    }
}
