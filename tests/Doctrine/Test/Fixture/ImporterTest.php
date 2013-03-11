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

namespace Doctrine\Test\Fixture;

use Doctrine\Fixture\Importer;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Sorter\CalculatorFactory;
use Doctrine\Fixture\Loader\ClassLoader;
use Doctrine\Common\EventManager;

/**
 * Importer tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Configuration
     */
    private $configuration;

    /**
     * @var \Doctrine\Fixture\Importer
     */
    private $importer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->configuration = new Configuration();

        $this->configuration->setEventManager(new EventManager());
        $this->configuration->setCalculatorFactory(new CalculatorFactory());

        $this->importer = new Importer($this->configuration);
    }

    /**
     * @dataProvider provideDataForImport
     */
    public function testImport($purge, $callsImport, $callsPurge)
    {
        $mockFixture = $this->getMockBuilder('Doctrine\Test\Mock\Unassigned\FixtureA')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($callsImport)
                 ->method('import');

        $mockFixture->expects($callsPurge)
                 ->method('purge');

        $loader = new ClassLoader(array($mockFixture));

        $this->importer->import($loader, $purge);
    }

    public function provideDataForImport()
    {
        return array(
            array(false, $this->once(), $this->never()),
            array(true, $this->once(), $this->once()),
        );
    }
}