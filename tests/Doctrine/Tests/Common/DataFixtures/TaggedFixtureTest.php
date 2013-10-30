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
use Doctrine\Common\DataFixtures\TaggedFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Test Fixture tagging.
 *
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 */
class TaggedFixtureTest extends BaseTest
{
    public function testFixtureOrder()
    {
        $loader = new Loader(array('foo', 'bar'));
        $loader->addFixture(new TaggedFixture1);
        $loader->addFixture(new TaggedFixture2);
        $loader->addFixture(new TaggedFixture3);
        $loader->addFixture(new NotTaggedFixture1);

        $taggedFixtures = $loader->getFixtures();

        $this->assertCount(3, $taggedFixtures);
    }
}

class TaggedFixture1 implements FixtureInterface, TaggedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getTags()
    {
        return array('foo', 'bar', 'boo');
    }
}

class TaggedFixture2 implements FixtureInterface, TaggedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getTags()
    {
        return array('bar', 'boo');
    }
}

class TaggedFixture3 implements FixtureInterface, TaggedFixtureInterface
{
    public function load(ObjectManager $manager)
    {}

    public function getTags()
    {
        return array('boo');
    }
}

class NotTaggedFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {}
}
