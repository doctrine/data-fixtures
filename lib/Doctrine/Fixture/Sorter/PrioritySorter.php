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
 * PrioritySorter is a queueing algorithm by prioritization, implemented using
 * a specialized version of max-heap.
 * A max-heap is a data structure that given a node, all nodes beneath it have
 * a lower value than it.
 * This class acts more like a delegate over SplPriorityQueue with necessary
 * methods to properly work on its purposes.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class PrioritySorter implements \Iterator, \Countable
{
    /**
     * @var \SplPriorityQueue
     */
    private $queue;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->queue = new \SplPriorityQueue();

        $this->queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
    }

    /**
     * Insert fixture with the priority in the sorter.
     *
     * @param \Doctrine\Fixture\Fixture $fixture
     * @param integer                   $priority
     */
    public function insert(Fixture $fixture, $priority)
    {
        $this->queue->insert($fixture, $priority);
    }

    /**
     * Checks whether the sorter is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->queue->isEmpty();
    }

    /**
     * Return a valid order list of all current nodes.
     * The desired priority sorting is the reverse post-order of extractions.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @return array<\Doctrine\Fixture\Fixture>
     */
    public function sort()
    {
        $sortedList = array();

        while ($this->queue->valid()) {
            $sortedList[] = $this->queue->extract();
        }

        return array_reverse($sortedList);
    }

    /**
     * Counts the number of elements in the sorter.
     *
     * {@internal Implementation for Countable interface}
     *
     * @return integer
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * Return current node pointed by the iterator.
     *
     * {@internal Implementation for Iterator interface}
     *
     * @return mixed
     */
    public function current()
    {
        return $this->queue->current();
    }

    /**
     * Return current node index.
     *
     * {@internal Implementation for Iterator interface}
     *
     * @return mixed
     */
    public function key()
    {
        return $this->queue->key();
    }

    /**
     * Move to the next node.
     *
     * {@internal Implementation for Iterator interface}
     */
    public function next()
    {
        return $this->queue->next();
    }

    /**
     * Rewind iterator back to the start (no-op).
     *
     * {@internal Implementation for Iterator interface}
     */
    public function rewind()
    {
        return $this->queue->rewind();
    }

    /**
     * Check whether the queue contains more nodes
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->queue->valid();
    }
}