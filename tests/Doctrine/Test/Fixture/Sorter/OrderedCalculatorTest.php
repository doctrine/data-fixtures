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

use Doctrine\Fixture\Sorter\OrderedCalculator;
use Doctrine\Test\Mock;

/**
 * OrderedCalculator tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class OrderedCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\OrderedCalculator
     */
    private $calculator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->calculator = new OrderedCalculator();
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
        $orderedFixtureA   = new Mock\Ordered\FixtureA();
        $orderedFixtureB   = new Mock\Ordered\FixtureB();
        $fixtureB          = new Mock\Unassigned\FixtureB();

        return array(
            // Single
            array(true, array($orderedFixtureB)),
            // Multi
            array(true, array(
                $orderedFixtureA,
                $fixtureB,
                $orderedFixtureB,
            )),
            // Unassigned only
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
        $orderedFixtureA   = new Mock\Ordered\FixtureA();
        $orderedFixtureB   = new Mock\Ordered\FixtureB();
        $orderedFixtureC   = new Mock\Ordered\FixtureC();
        $fixtureA          = new Mock\Unassigned\FixtureA();
        $fixtureB          = new Mock\Unassigned\FixtureB();

        return array(
            // Single
            array(
                array($orderedFixtureB),
                array($orderedFixtureB),
            ),
            // Multi
            array(
                array(
                    $orderedFixtureB,
                    $orderedFixtureC,
                ),
                array(
                    $orderedFixtureB,
                    $orderedFixtureC,
                ),
            ),
            // Unassigned only (LIFO caused by PrioritySorter::sort)
            array(
                array($fixtureA, $fixtureB),
                array($fixtureB, $fixtureA),
            ),
            // Mixed
            array(
                array(
                    $fixtureB,
                    $orderedFixtureB,
                    $orderedFixtureC,
                    $orderedFixtureA,
                ),
                array(
                    $fixtureB,
                    $orderedFixtureA,
                    $orderedFixtureB,
                    $orderedFixtureC,
                ),
            ),
        );
    }
}
