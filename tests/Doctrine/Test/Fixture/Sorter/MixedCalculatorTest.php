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

    public function testSuccessAcceptOneOfEachFixture()
    {
        $fixtureList = array(
            new Mock\DependentFixtureA(),
            new Mock\FixtureB(),
            new Mock\OrderedFixtureA(),
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testSuccessAcceptMultiFixture()
    {
        $fixtureList = array(
            new Mock\DependentFixtureA(),
            new Mock\DependentFixtureB(),
            new Mock\DependentFixtureC(),
            new Mock\FixtureA(),
            new Mock\FixtureB(),
            new Mock\FixtureC(),
            new Mock\OrderedFixtureA(),
            new Mock\OrderedFixtureB(),
            new Mock\OrderedFixtureC(),
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testFailureAcceptFixtureUnassigned()
    {
        $fixtureList = array(
            new Mock\FixtureB()
        );

        $this->assertFalse($this->calculator->accept($fixtureList));
    }

    public function testFailureAcceptFixtureDependent()
    {
        $fixtureList = array(
            new Mock\DependentFixtureA(),
            new Mock\FixtureB()
        );

        $this->assertFalse($this->calculator->accept($fixtureList));
    }

    public function testFailureAcceptFixtureOrdered()
    {
        $fixtureList = array(
            new Mock\OrderedFixtureA(),
            new Mock\FixtureB()
        );

        $this->assertFalse($this->calculator->accept($fixtureList));
    }

    public function testSuccessCalculatorMultiFixture()
    {
        $dependentFixtureB = new Mock\DependentFixtureB();
        $orderedFixtureB   = new Mock\OrderedFixtureB();

        $fixtureList = array($dependentFixtureB, $orderedFixtureB);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($orderedFixtureB, $dependentFixtureB);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorWithFixtureNotDependent()
    {
        $dependentFixtureA = new Mock\DependentFixtureA();
        $dependentFixtureB = new Mock\DependentFixtureB();
        $dependentFixtureC = new Mock\DependentFixtureC();
        $orderedFixtureA   = new Mock\OrderedFixtureA();
        $orderedFixtureB   = new Mock\OrderedFixtureB();
        $orderedFixtureC   = new Mock\OrderedFixtureC();
        $fixtureA          = new Mock\FixtureA();
        $fixtureB          = new Mock\FixtureB();
        $fixtureC          = new Mock\FixtureC();

        $fixtureList = array(
            $dependentFixtureA,
            $dependentFixtureB,
            $dependentFixtureC,
            $orderedFixtureA,
            $orderedFixtureB,
            $orderedFixtureC,
            $fixtureA,
            $fixtureB,
            $fixtureC,
        );

        $sortedList  = $this->calculator->calculate($fixtureList);

        $correctList = array(
            $orderedFixtureB,
            $orderedFixtureC,
            $orderedFixtureA,
            $fixtureB,
            $dependentFixtureA,
            $dependentFixtureB,
            $dependentFixtureC,
            $fixtureA,
            $fixtureC,
        );

        $this->assertSame($correctList, $sortedList);
    }
}
