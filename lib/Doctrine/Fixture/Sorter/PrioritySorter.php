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
 * PrioritySorter is a queueing algorithm by prioritization, implemented using
 * a specialized version of max-heap.
 * A max-heap is a data structure that given a node, all nodes beneath it have
 * a lower value than it.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class PrioritySorter extends \SplPriorityQueue
{
    /**
     * Constructor.
     *
     * {@internal SplPriorityQueue does not have a constructor natively. That
     *            is the reason why you cannot call parent::__construct() in
     *            constructor.}
     */
    public function __construct()
    {
        $this->setExtractFlags(self::EXTR_DATA);
    }

    /**
     * Return a valid order list of all current nodes.
     * The desired priority sorting is the reverse postorder of extractions.
     *
     * {@internal Highly performance-sensitive method.}
     *
     * @return array<Doctrine\Fixture\Fixture>
     */
    public function sort()
    {
        $sortedList = array();

        while ($this->valid()) {
            $sortedList[] = $this->extract();
        }

        return array_reverse($sortedList);
    }
}