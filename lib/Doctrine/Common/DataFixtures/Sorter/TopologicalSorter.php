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

namespace Doctrine\Common\DataFixtures\Sorter;

/**
 * TopologicalSorter is an ordering algorithm for directed graphs (DG) and/or
 * directed acyclic graphs (DAG) by using a depth-first searching (DFS) to
 * traverse the graph built in memory.
 * This algorithm have a linear running time based on nodes (V) and dependency
 * between the nodes (E), resulting in a computational complexity of O(V + E).
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class TopologicalSorter
{
    const NOT_VISITED = 0;
    const IN_PROGRESS = 1;
    const VISITED     = 2;

    /**
     * Matrix of nodes (aka. vertex).
     * Keys are provided hashes and values are the node definition objects.
     *
     * The node state definition contains the following properties:
     *
     * - <b>state</b> (integer)
     * Whether the node is NOT_VISITED or IN_PROGRESS
     *
     * - <b>value</b> (object)
     * Actual node value
     *
     * - <b>dependencyList</b> (array<string>)
     * Map of node dependencies defined as hashes.
     *
     * @var array<stdClass>
     */
    private $nodeList = array();

    /**
     * Volatile variable holding calculated nodes during sorting process.
     *
     * @var array
     */
    private $sortedNodeList = array();

    /**
     * Adds a new node (vertex) to the graph, assigning its hash and value.
     *
     * @param string $hash
     * @param object $node
     *
     * @return void
     */
    public function addNode($hash, $node)
    {
        $definition = new \stdClass();

        $definition->state          = self::NOT_VISITED;
        $definition->value          = $node;
        $definition->dependencyList = array();

        $this->nodeList[$hash] = $definition;
    }

    /**
     * Checks the existence of a node in the graph.
     *
     * @param string $hash
     *
     * @return bool
     */
    public function hasNode($hash)
    {
        return isset($this->nodeList[$hash]);
    }

    /**
     * Adds a new dependency (edge) to the graph using their hashes.
     *
     * @param string $fromNode
     * @param string $toNode
     *
     * @return void
     */
    public function addDependency($fromHash, $toHash)
    {
        $definition = $this->nodeList[$fromHash];

        $definition->dependencyList[] = $toHash;
    }

    /**
     * Sets the dependency list (edges) to the graph using their hashes.
     *
     * @param string $fromHash
     * @param array  $dependencyList
     *
     * @return void
     */
    public function setDependencyList($fromHash, array $dependencyList)
    {
        $definition = $this->nodeList[$fromHash];

        $definition->dependencyList = $dependencyList;
    }

    /**
     * Return a valid order list of all current nodes.
     * The desired topological sorting is the postorder of these searches.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @return array
     */
    public function sort()
    {
        foreach ($this->nodeList as $definition) {
            if ($definition->state !== self::NOT_VISITED) {
                continue;
            }

            $this->visit($definition);
        }

        $sortedList = $this->sortedNodeList;

        $this->nodeList       = array();
        $this->sortedNodeList = array();

        return $sortedList;
    }

    /**
     * Visit a given node definition for reordering.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @throws \RuntimeException
     *
     * @param \stdClass $definition
     */
    private function visit($definition)
    {
        $definition->state = self::IN_PROGRESS;

        foreach ($definition->dependencyList as $dependency) {
            if ( ! isset($this->nodeList[$dependency])) {
                throw new \RuntimeException(
                    sprintf(
                        'Fixture "%s" has a dependency of fixture "%s", but it not listed to be loaded.',
                        get_class($definition->value),
                        $dependency
                    )
                );
            }

            $childDefinition = $this->nodeList[$dependency];

            switch ($childDefinition->state) {
                case self::VISITED:
                    continue;

                case self::IN_PROGRESS:
                    $message = 'Graph contains cyclic dependency. An example of this problem would be the following: '
                        . 'Class C has class B as its dependency. Then, class B has class A has its dependency. '
                        . 'Finally, class A has class C as its dependency.';

                    throw new \RuntimeException($message);

                case self::NOT_VISITED:
                    $this->visit($childDefinition);
            }
        }

        $definition->state = self::VISITED;

        $this->sortedNodeList[] = $definition->value;
    }
}
