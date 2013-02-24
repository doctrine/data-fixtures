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

use Doctrine\Fixture\Loader\ChainLoader;
use Doctrine\Fixture\Loader\DirectoryLoader;
use Doctrine\Fixture\Loader\ClassLoader;

/**
 * DirectoryLoader tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ChainLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $loader = new ChainLoader(array(
            new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB')),
            new DirectoryLoader(realpath(__DIR__ . '/../../Mock/Dependent')),
        ));

        $fixtureList = $loader->load();

        $this->assertCount(4, $fixtureList);
    }

    public function testGetLoaderList()
    {
        $loader = new ChainLoader(array(
            new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB')),
        ));

        $loaderList = $loader->getLoaderList();

        $this->assertCount(1, $loaderList);
        $this->assertInstanceOf('Doctrine\Fixture\Loader\ClassLoader', reset($loaderList));
    }

    public function testAddLoader()
    {
        $loader = new ChainLoader(array(
            new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB')),
        ));

        $this->assertCount(1, $loader->getLoaderList());

        $loader->addLoader(new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA')));

        $this->assertCount(2, $loader->getLoaderList());
    }

    public function testRemoveLoader()
    {
        $classLoaderA = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureA'));
        $classLoaderB = new ClassLoader(array('Doctrine\Test\Mock\Unassigned\FixtureB'));

        $loader = new ChainLoader(array(
            $classLoaderA,
            $classLoaderB,
        ));

        $this->assertCount(2, $loader->getLoaderList());

        $loader->removeLoader($classLoaderA);

        $this->assertCount(1, $loader->getLoaderList());
    }
}
