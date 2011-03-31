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

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\OrderedByParentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;

require_once __DIR__.'/TestInit.php';

/**
 * Test Fixture ordering.
 *
 * @author Gustavo Adrian <comfortablynumb84@gmail.com>
 */
class OrderedByParentFixtureTest extends BaseTest
{
    public function test_FixtureOrder()
    {
        $loader = new Loader();
        $loader->addFixture(new OrderedByParentFixture3);
        $loader->addFixture(new OrderedByParentFixture1);
        $loader->addFixture(new OrderedByParentFixture2);
        $loader->addFixture(new BaseParentFixture1);

        $orderedFixtures = $loader->getFixtures();
        $this->assertEquals(4, count($orderedFixtures));

        $this->assertTrue(array_shift($orderedFixtures) instanceof BaseParentFixture1);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture1);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture2);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture3);
    }
}

class OrderedByParentFixture1 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClass()
    {
        return 'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1';
    }
}

class OrderedByParentFixture2 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClass()
    {
        return 'Doctrine\Tests\Common\DataFixtures\OrderedByParentFixture1';
    }
}

class OrderedByParentFixture3 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClass()
    {
        return 'Doctrine\Tests\Common\DataFixtures\OrderedByParentFixture2';
    }
}

class BaseParentFixture1 implements FixtureInterface
{
    public function load($manager)
    {}
}
