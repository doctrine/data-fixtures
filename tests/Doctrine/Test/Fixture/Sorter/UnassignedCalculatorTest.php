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

    public function testSuccessAcceptSingleFixture()
    {
        $fixtureList = array(
            new Mock\FixtureA()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testSuccessAcceptMultiFixture()
    {
        $fixtureList = array(
            new Mock\FixtureA(),
            new Mock\FixtureB()
        );

        $this->assertTrue($this->calculator->accept($fixtureList));
    }

    public function testSuccessCalculateSingleFixture()
    {
        $fixtureA = new Mock\FixtureA();

        $fixtureList = array($fixtureA);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureA);

        $this->assertSame($correctList, $sortedList);
    }

    public function testSuccessCalculatorMultiFixture()
    {
        $fixtureA = new Mock\FixtureA();
        $fixtureB = new Mock\FixtureB();

        $fixtureList = array($fixtureB, $fixtureA);

        $sortedList  = $this->calculator->calculate($fixtureList);
        $correctList = array($fixtureB, $fixtureA);

        $this->assertSame($correctList, $sortedList);
    }
}
