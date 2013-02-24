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

namespace Doctrine\Test\Fixture\Sorter;

use Doctrine\Fixture\Sorter\CalculatorFactory;
use Doctrine\Test\Mock;

/**
 * CalculatorFactory tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class CalculatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\CalculatorFactory
     */
    private $calculatorFactory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calculatorFactory = new CalculatorFactory();
    }

    /**
     * @dataProvider provideDataForGetCalculator
     */
    public function testGetCalculator($calculatorClass, $fixtureList)
    {
        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf($calculatorClass, $calculator);
    }

    public function provideDataForGetCalculator()
    {
        $dependentFixtureA = new Mock\Dependent\FixtureA();
        $dependentFixtureB = new Mock\Dependent\FixtureB();
        $dependentFixtureC = new Mock\Dependent\FixtureC();
        $orderedFixtureA   = new Mock\Ordered\FixtureA();
        $orderedFixtureB   = new Mock\Ordered\FixtureB();
        $orderedFixtureC   = new Mock\Ordered\FixtureC();
        $fixtureA          = new Mock\Unassigned\FixtureA();
        $fixtureB          = new Mock\Unassigned\FixtureB();
        $fixtureC          = new Mock\Unassigned\FixtureC();

        return array(
            // Mixed
            array('Doctrine\Fixture\Sorter\MixedCalculator', array(
                $orderedFixtureA,
                $dependentFixtureB,
            )),
            array('Doctrine\Fixture\Sorter\MixedCalculator', array(
                $fixtureA,
                $fixtureB,
                $orderedFixtureA,
                $dependentFixtureB,
            )),
            // Ordered
            array('Doctrine\Fixture\Sorter\OrderedCalculator', array(
                $orderedFixtureA,
                $orderedFixtureB,
                $orderedFixtureC,
            )),
            array('Doctrine\Fixture\Sorter\OrderedCalculator', array(
                $fixtureA,
                $fixtureB,
                $orderedFixtureA,
            )),
            // Dependent
            array('Doctrine\Fixture\Sorter\DependentCalculator', array(
                $dependentFixtureA,
                $dependentFixtureB,
                $dependentFixtureC,
            )),
            array('Doctrine\Fixture\Sorter\DependentCalculator', array(
                $fixtureA,
                $fixtureB,
                $dependentFixtureA,
            )),
            // Unassigned
            array('Doctrine\Fixture\Sorter\UnassignedCalculator', array(
                $fixtureA,
                $fixtureB,
                $fixtureC,
            )),
        );
    }
}