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

use Doctrine\Fixture\Fixture;

/**
 * Calculate the order between fixtures by using a mixed sorting.
 * Sorting may be composed by ordered, dependent and unassigned fixtures all
 * together.
 * Return order always ordered, unassigned and dependent, where unassigned and
 * dependent may be mixed together as a result of the graph of dependencies.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class MixedCalculator implements Calculator
{
    /**
     * {@inheritdoc}
     */
    public function accept(array $fixtureList)
    {
        $containsDependent = false;
        $containsOrdered   = false;

        foreach ($fixtureList as $fixture) {
            switch (true) {
                case $this->isDependent($fixture):
                    $containsDependent = true;
                    break;

                case $this->isOrdered($fixture):
                    $containsOrdered = true;
                    break;

                default:
                    // Do nothing
            }
        }

        return ($containsDependent && $containsOrdered);
    }

    /**
     * {@inheritdoc}
     *
     * {@internal Highly performance-sensitive method.}
     */
    public function calculate(array $fixtureList)
    {
        $orderedFixtureList   = $this->calculateOrdered($fixtureList);
        $remainingFixtureList = array();

        foreach ($fixtureList as $fixture) {
            if (in_array($fixture, $orderedFixtureList)) {
                continue;
            }

            $remainingFixtureList[] = $fixture;
        }

        $dependentFixtureList = $this->calculateDependent($remainingFixtureList);

        return array_merge($orderedFixtureList, $dependentFixtureList);
    }

    /**
     * Processes the fixture list and returns a subset of given list,
     * containing only ordered fixtures.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @param array $fixtureList
     *
     * @return array
     */
    private function calculateOrdered(array $fixtureList)
    {
        $sorter = new PrioritySorter();

        foreach ($fixtureList as $fixture) {
            if ( ! $this->isOrdered($fixture)) {
                continue;
            }

            $sorter->insert($fixture, $fixture->getOrder());
        }

        return $sorter->sort();
    }

    /**
     * Processes the fixture list and returns a subset of given list,
     * containing only dependent and unassigned fixtures.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @param array $fixtureList
     *
     * @return array
     */
    private function calculateDependent(array $fixtureList)
    {
        $sorter = new TopologicalSorter();

        foreach ($fixtureList as $fixture) {
            // Adding nodes
            $fixtureHash = get_class($fixture);

            $sorter->addNode($fixtureHash, $fixture);

            if ( ! $this->isDependent($fixture)) {
                continue;
            }

            // Adding dependencies
            foreach ($fixture->getDependencyList() as $dependencyClass) {
                $sorter->addDependency($fixtureHash, $dependencyClass);
            }
        }

        return $sorter->sort();
    }

    /**
     * Checks if a given Fixture is a DependentFixture.
     *
     * @param \Doctrine\Fixture\Fixture $fixture
     *
     * @return boolean
     */
    private function isDependent(Fixture $fixture)
    {
        return ($fixture instanceof DependentFixture);
    }

    /**
     * Checks if a given Fixture is an OrderedFixture.
     *
     * @param \Doctrine\Fixture\Fixture $fixture
     *
     * @return boolean
     */
    private function isOrdered(Fixture $fixture)
    {
        return ($fixture instanceof OrderedFixture);
    }
}