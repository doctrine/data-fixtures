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

namespace Doctrine\Fixture\Sorter;

/**
 * Calculate the best sorter to handle fixture execution order.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class CalculatorFactory
{
    /**
     * Holds a list of available calculators.
     *
     * @var array<Doctrine\Fixture\Sorter\Calculator>
     */
    private $calculatorList = array();

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->addCalculator(new MixedCalculator());
        $this->addCalculator(new OrderedCalculator());
        $this->addCalculator(new DependentCalculator());
    }

    /**
     * Given a list of fixtures, retrieve the calculator sorter that can best
     * handle the execution re-ordering of fixtures.
     *
     * @param array $fixtureList
     *
     * @return \Doctrine\Fixture\Sorter\Calculator
     */
    public function getCalculator(array $fixtureList)
    {
        foreach ($this->calculatorList as $calculator) {
            if ( ! $calculator->accept($fixtureList)) {
                continue;
            }

            return $calculator;
        }

        // Fallback, return fixtures the same way they were provided.
        return new UnassignedCalculator();
    }

    /**
     * Adds a new fixture re-ordering calculator.
     *
     * @param \Doctrine\Fixture\Sorter\Calculator $calculator
     *
     * @return \Doctrine\Fixture\Sorter\CalculatorFactory
     */
    public function addCalculator(Calculator $calculator)
    {
        $this->calculatorList[] = $calculator;

        return $this;
    }
}