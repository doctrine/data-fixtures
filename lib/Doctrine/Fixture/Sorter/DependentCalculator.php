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
 * Calculate the dependency between fixtures by using a graph.
 * Dependency is calculated using a Depth-First Searching (DFS), in this case,
 * Topological Sorting.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class DependentCalculator implements Calculator
{
    /**
     * {@inheritdoc}
     */
    public function accept(array $fixtureList)
    {
        foreach ($fixtureList as $fixture) {
            if ( ! $this->isDependent($fixture)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * {@internal Highly performance-sensitive method.}
     */
    public function calculate(array $fixtureList)
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
}