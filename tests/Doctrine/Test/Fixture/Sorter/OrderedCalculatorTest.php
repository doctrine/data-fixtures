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
use Doctrine\Fixture\Sorter\OrderedFixture;
use Doctrine\Fixture\Fixture;

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
            new OrderedCalculatorTestFixtureC()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testSuccessAcceptMultiFixture()
    {
        $fixtureList = array(
            new OrderedCalculatorTestFixtureA(),
            new OrderedCalculatorTestFixtureB(),
            new OrderedCalculatorTestFixtureC()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testFailureAcceptFixture()
    {
        $fixtureList = array(
            new OrderedCalculatorTestFixtureB()
        );

        $this->assertFalse($this->calculator->accept($fixtureList));
    }

    public function testSuccessCalculateSingleFixture()
    {
        $fixtureC = new OrderedCalculatorTestFixtureC();

        $fixtureList = array($fixtureC);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureC);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculateSingleFixtureNotDependent()
    {
        $fixtureB = new OrderedCalculatorTestFixtureB();

        $fixtureList = array($fixtureB);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorMultiFixture()
    {
        $fixtureC = new OrderedCalculatorTestFixtureC();
        $fixtureD = new OrderedCalculatorTestFixtureD();

        $fixtureList = array($fixtureD, $fixtureC);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureC, $fixtureD);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorWithFixtureNotDependent()
    {
        $fixtureA = new OrderedCalculatorTestFixtureA();
        $fixtureB = new OrderedCalculatorTestFixtureB();
        $fixtureC = new OrderedCalculatorTestFixtureC();
        $fixtureD = new OrderedCalculatorTestFixtureD();

        $fixtureList = array($fixtureD, $fixtureB, $fixtureC, $fixtureA);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB, $fixtureC, $fixtureA, $fixtureD);

        $this->assertSame($correctList, $sortedList);
    }
}

class OrderedCalculatorTestFixtureA implements OrderedFixture
{
    public function getOrder()
    {
        return 2;
    }

    public function import()
    {
    }
}

class OrderedCalculatorTestFixtureB implements Fixture
{
    public function import()
    {
    }
}

class OrderedCalculatorTestFixtureC implements OrderedFixture
{
    public function getOrder()
    {
        return 1;
    }

    public function import()
    {
    }
}

class OrderedCalculatorTestFixtureD implements OrderedFixture
{
    public function getOrder()
    {
        return 2;
    }

    public function import()
    {
    }
}