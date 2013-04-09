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

use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Sorter\CalculatorFactory;
use Doctrine\Fixture\Loader\ClassLoader;
use Doctrine\Fixture\Filter\GroupedFilter;
use Doctrine\Common\EventManager;

/**
 * Executor tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Configuration
     */
    private $configuration;

    /**
     * @var \Doctrine\Fixture\Executor
     */
    private $executor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->configuration = new Configuration();

        $this->configuration->setEventManager(new EventManager());
        $this->configuration->setCalculatorFactory(new CalculatorFactory());

        $this->executor = new Executor($this->configuration);
    }

    /**
     * @dataProvider provideDataForExecute
     */
    public function testExecute($flags, $callsImport, $callsPurge)
    {
        $mockFixture = $this->getMockedFixture('Doctrine\Test\Mock\Unassigned\FixtureA', $callsImport, $callsPurge);

        $loader = new ClassLoader(array($mockFixture));

        $this->executor->execute($loader, $flags);
    }

    public function provideDataForExecute()
    {
        return array(
            array(Executor::IMPORT, $this->once(), $this->never()),
            array(Executor::PURGE, $this->never(), $this->once()),
            array(Executor::IMPORT | Executor::PURGE, $this->once(), $this->once()),
        );
    }

    /**
     * @dataProvider provideDataForFilteredExecute
     */
    public function testFilteredExecute($onlyImplementors, $callsUnassignedImport)
    {
        $this->configuration->setFilter(new GroupedFilter(array('test'), $onlyImplementors));

        $mockUnassignedFixtureA = $this->getMockedFixture(
            'Doctrine\Test\Mock\Unassigned\FixtureB',
            $callsUnassignedImport,
            $this->never()
        );
        $mockGroupedFixtureA    = $this->getMockedFixture(
            'Doctrine\Test\Mock\Grouped\FixtureA',
            $this->once(),
            $this->never()
        );

        $mockGroupedFixtureA
            ->expects($this->once())
            ->method('getGroupList')
            ->will($this->returnValue(array('test')));

        $loader = new ClassLoader(array(
            $mockUnassignedFixtureA,
            $mockGroupedFixtureA
        ));

        $this->executor->execute($loader, Executor::IMPORT);
    }

    public function provideDataForFilteredExecute()
    {
        return array(
            array(true, $this->never()),
            array(false, $this->once()),
        );
    }

    private function getMockedFixture($className, $callsImport, $callsPurge)
    {
        $mockFixture = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFixture->expects($callsImport)
                 ->method('import');

        $mockFixture->expects($callsPurge)
                 ->method('purge');

        return $mockFixture;
    }
}