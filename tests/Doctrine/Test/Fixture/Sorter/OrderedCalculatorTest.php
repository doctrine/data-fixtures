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

    public function testSuccessAcceptSingleFixture()
    {
        $fixtureList = array(
            new Mock\OrderedFixtureB()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testSuccessAcceptMultiFixture()
    {
        $fixtureList = array(
            new Mock\OrderedFixtureA(),
            new Mock\FixtureB(),
            new Mock\OrderedFixtureB()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testFailureAcceptFixture()
    {
        $fixtureList = array(
            new Mock\FixtureB()
        );

        $this->assertFalse($this->calculator->accept($fixtureList));
    }

    public function testSuccessCalculateSingleFixture()
    {
        $fixtureB = new Mock\OrderedFixtureB();

        $fixtureList = array($fixtureB);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculateSingleFixtureNotDependent()
    {
        $fixtureB = new Mock\FixtureB();

        $fixtureList = array($fixtureB);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorMultiFixture()
    {
        $fixtureB = new Mock\OrderedFixtureB();
        $fixtureC = new Mock\OrderedFixtureC();

        $fixtureList = array($fixtureC, $fixtureB);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB, $fixtureC);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorWithFixtureNotDependent()
    {
        $fixtureA = new Mock\OrderedFixtureA();
        $fixtureZ = new Mock\FixtureB();
        $fixtureB = new Mock\OrderedFixtureB();
        $fixtureC = new Mock\OrderedFixtureC();

        $fixtureList = array($fixtureC, $fixtureZ, $fixtureB, $fixtureA);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureZ, $fixtureB, $fixtureA, $fixtureC);

        $this->assertSame($correctList, $sortedList);
    }
}
