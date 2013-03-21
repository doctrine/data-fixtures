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

use Doctrine\Fixture\Loader\RecursiveDirectoryLoader;

/**
 * RecursiveDirectoryLoader tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class RecursiveDirectoryLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSuccess()
    {
        new RecursiveDirectoryLoader(__DIR__);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorFailure()
    {
        new RecursiveDirectoryLoader(null);
    }

    /**
     * @dataProvider provideDataForLoad
     */
    public function testLoad($count, $directory)
    {
        $loader = new RecursiveDirectoryLoader($directory);

        $fixtureList = $loader->load();

        $this->assertCount($count, $fixtureList);
    }

    public function provideDataForLoad()
    {
        return array(
            // No fixture files
            array(0, __DIR__),
            // Branch folder
            array(12, realpath(__DIR__ . '/../../Mock')),
            // Leaf folder
            array(3, realpath(__DIR__ . '/../../Mock/Unassigned')),
        );
    }

    public function testGetDirectory()
    {
        $loader = new RecursiveDirectoryLoader(__DIR__);

        $directory = $loader->getDirectory();

        $this->assertEquals(__DIR__, $directory);
    }
}
