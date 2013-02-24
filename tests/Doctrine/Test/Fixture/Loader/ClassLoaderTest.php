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

namespace Doctrine\Test\Fixture\Loader;

use Doctrine\Fixture\Loader\ClassLoader;

/**
 * ClassLoader tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideDataForLoad
     */
    public function testLoad($count, $fixtureClassList)
    {
        $loader = new ClassLoader($fixtureClassList);

        $fixtureList = $loader->load();

        $this->assertCount($count, $fixtureList);
    }

    public function provideDataForLoad()
    {
        return array(
            // Empty array
            array(0, array()),
            // Single element
            array(1, array('Doctrine\Test\Mock\Unassigned\FixtureA')),
            // Multi element
            array(3, array(
                'Doctrine\Test\Mock\Unassigned\FixtureA',
                'Doctrine\Test\Mock\Unassigned\FixtureB',
                'Doctrine\Test\Mock\Unassigned\FixtureC',
            )),
            // Skip transient
            array(1, array(
                'Doctrine\Test\Mock\Unassigned\FixtureA',
                get_parent_class(__CLASS__),
                __CLASS__,
            )),
            // Duplicated class
            array(1, array(
                'Doctrine\Test\Mock\Unassigned\FixtureA',
                'Doctrine\Test\Mock\Unassigned\FixtureA',
                'Doctrine\Test\Mock\Unassigned\FixtureA',
            )),
        );
    }

    public function testGetClassList()
    {
        $loader = new ClassLoader(array(
            'Doctrine\Test\Mock\Unassigned\FixtureA'
        ));

        $fixtureList = $loader->getClassList();

        $this->assertCount(1, $fixtureList);
        $this->assertInternalType('string', reset($fixtureList));
    }

    public function testAddClass()
    {
        $loader = new ClassLoader(array(
            'Doctrine\Test\Mock\FixtureA'
        ));

        $this->assertCount(1, $loader->getClassList());

        $loader->addClass('Doctrine\Test\Mock\Unassigned\FixtureB');

        $this->assertCount(2, $loader->getClassList());
    }

    public function testRemoveClass()
    {
        $loader = new ClassLoader(array(
            'Doctrine\Test\Mock\Unassigned\FixtureA',
            'Doctrine\Test\Mock\Unassigned\FixtureB',
            'Doctrine\Test\Mock\Unassigned\FixtureC',
        ));

        $this->assertCount(3, $loader->getClassList());

        $loader->removeClass('Doctrine\Test\Mock\Unassigned\FixtureB');

        $this->assertCount(2, $loader->getClassList());
    }
}
