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

use Doctrine\Fixture\Sorter\DependentCalculator;
use Doctrine\Test\Mock;

/**
 * DependentCalculator tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DependentCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\DependentCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calculator = new DependentCalculator();
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
        $fixtureB          = new Mock\Unassigned\FixtureB();

        return array(
            // Single
            array(true, array(
                $dependentFixtureB,
            )),
            // Multi
            array(true, array(
                $dependentFixtureA,
                $fixtureB,
                $dependentFixtureB,
            )),
            // Failure
            array(false, array(
                $fixtureB,
            )),
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
        $fixtureB          = new Mock\Unassigned\FixtureB();

        return array(
            // Single
            array(
                array($dependentFixtureB),
                array($dependentFixtureB),
            ),
            // Multi
            array(
                array(
                    $dependentFixtureB,
                    $dependentFixtureC,
                ),
                array(
                    $dependentFixtureC,
                    $dependentFixtureB,
                )
            ),
            // Unassigned
            array(
                array($fixtureB),
                array($fixtureB),
            ),
            // Mixed
            array(
                array(
                    $dependentFixtureB,
                    $dependentFixtureC,
                    $fixtureB,
                    $dependentFixtureA,
                ),
                array(
                    $dependentFixtureC,
                    $fixtureB,
                    $dependentFixtureB,
                    $dependentFixtureA,
                ),
            ),
        );
    }
}
