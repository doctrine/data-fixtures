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
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Test Fixture ordering.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class OrderedFixtureTest extends BaseTest
{
    public function testFixtureOrder()
    {
        $loader = new Loader();
        $loader->addFixture(new OrderedFixture1);
        $loader->addFixture(new OrderedFixture2);
        $loader->addFixture(new OrderedFixture3);
        $loader->addFixture(new BaseFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertCount(4, $orderedFixtures);
        $this->assertInstanceOf(BaseFixture1::class, $orderedFixtures[0]);
        $this->assertInstanceOf(OrderedFixture2::class, $orderedFixtures[1]);
        $this->assertInstanceOf(OrderedFixture1::class, $orderedFixtures[2]);
        $this->assertInstanceOf(OrderedFixture3::class, $orderedFixtures[3]);
    }
}

class OrderedFixture1 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder(): int
    {
        return 5;
    }
}

class OrderedFixture2 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder(): int
    {
        return 2;
    }
}

class OrderedFixture3 implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getOrder(): int
    {
        return 8;
    }
}

class BaseFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {}
}
