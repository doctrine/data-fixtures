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

    public function testMixedFixture()
    {
        $fixtureList = array(
            new Mock\OrderedFixtureA(),
            new Mock\DependentFixtureB(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\MixedCalculator', $calculator);
    }

    public function testMixedFixtureMixed()
    {
        $fixtureList = array(
            new Mock\FixtureA(),
            new Mock\FixtureB(),
            new Mock\OrderedFixtureA(),
            new Mock\DependentFixtureB(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\MixedCalculator', $calculator);
    }

    public function testOrderedFixture()
    {
        $fixtureList = array(
            new Mock\OrderedFixtureA(),
            new Mock\OrderedFixtureB(),
            new Mock\OrderedFixtureC(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\OrderedCalculator', $calculator);
    }

    public function testOrderedFixtureMixed()
    {
        $fixtureList = array(
            new Mock\FixtureA(),
            new Mock\FixtureB(),
            new Mock\OrderedFixtureA(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\OrderedCalculator', $calculator);
    }

    public function testDependentFixture()
    {
        $fixtureList = array(
            new Mock\DependentFixtureA(),
            new Mock\DependentFixtureB(),
            new Mock\DependentFixtureC(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\DependentCalculator', $calculator);
    }

    public function testDependentFixtureMixed()
    {
        $fixtureList = array(
            new Mock\FixtureA(),
            new Mock\FixtureB(),
            new Mock\DependentFixtureA(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\DependentCalculator', $calculator);
    }

    public function testUnassignedFixture()
    {
        $fixtureList = array(
            new Mock\FixtureA(),
            new Mock\FixtureB(),
            new Mock\FixtureC(),
        );

        $calculator = $this->calculatorFactory->getCalculator($fixtureList);

        $this->assertInstanceOf('Doctrine\Fixture\Sorter\UnassignedCalculator', $calculator);
    }
}