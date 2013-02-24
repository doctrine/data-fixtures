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

use Doctrine\Fixture\Sorter\UnassignedCalculator;
use Doctrine\Test\Mock;

/**
 * UnassignedCalculator tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class UnassignedCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\UnassignedCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calculator = new UnassignedCalculator();
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
        $fixtureA          = new Mock\Unassigned\FixtureA();
        $fixtureB          = new Mock\Unassigned\FixtureB();
        $fixtureC          = new Mock\Unassigned\FixtureC();

        return array(
            // Single
            array(true, array(
                $fixtureB,
            )),
            // Multi
            array(true, array(
                $fixtureA,
                $fixtureB,
                $fixtureC,
            )),
            // Mixed
            array(true, array(
                $dependentFixtureA,
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
        $fixtureA = new Mock\Unassigned\FixtureA();
        $fixtureB = new Mock\Unassigned\FixtureB();
        $fixtureC = new Mock\Unassigned\FixtureC();

        return array(
            // Single
            array(
                array($fixtureA),
                array($fixtureA),
            ),
            // Multi
            array(
                array(
                    $fixtureA,
                    $fixtureB,
                    $fixtureC,
                ),
                array(
                    $fixtureA,
                    $fixtureB,
                    $fixtureC,
                ),
            ),
        );
    }
}
