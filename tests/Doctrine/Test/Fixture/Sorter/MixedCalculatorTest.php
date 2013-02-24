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

use Doctrine\Fixture\Sorter\MixedCalculator;
use Doctrine\Test\Mock;

/**
 * MixedCalculator tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class MixedCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\MixedCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calculator = new MixedCalculator();
    }

    /**
     * @dataProvider provideDataForAccept
     */
    public function testAccept($expected, $fixtureList)
    {
        $this->assertEquals($expected, $this->calculator->accept($fixtureList));
    }

    public function provideDataForAccept()
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
            // One of each
            array(true, array(
                $dependentFixtureA,
                $fixtureB,
                $orderedFixtureA,
            )),
            // Multi
            array(true, array(
                $dependentFixtureA,
                $dependentFixtureB,
                $dependentFixtureC,
                $fixtureA,
                $fixtureB,
                $fixtureC,
                $orderedFixtureA,
                $orderedFixtureB,
                $orderedFixtureC,
            )),
            // Unassigned
            array(false, array(
                $fixtureB,
            )),
            // Dependent
            array(false, array(
                $dependentFixtureA,
                $fixtureB,
            )),
            // Ordered
            array(false, array(
                $orderedFixtureA,
                $fixtureB,
            ))
        );
    }

    /**
     * @dataProvider provideDataForCalculate
     */
    public function testCalculate($correctList, $fixtureList)
    {
        $this->assertSame($correctList, $this->calculator->calculate($fixtureList));
    }

    public function provideDataForCalculate()
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
            // Multi
            array(
                array(
                    $orderedFixtureB,
                    $dependentFixtureB,
                ),
                array(
                    $dependentFixtureB,
                    $orderedFixtureB,
                ),
            ),
            // Mixed
            array(
                array(
                    $orderedFixtureB,
                    $orderedFixtureC,
                    $orderedFixtureA,
                    $fixtureB,
                    $dependentFixtureA,
                    $dependentFixtureB,
                    $dependentFixtureC,
                    $fixtureA,
                    $fixtureC,
                ),
                array(
                    $dependentFixtureA,
                    $dependentFixtureB,
                    $dependentFixtureC,
                    $orderedFixtureA,
                    $orderedFixtureB,
                    $orderedFixtureC,
                    $fixtureA,
                    $fixtureB,
                    $fixtureC,
                ),
            ),
        );
    }
}
