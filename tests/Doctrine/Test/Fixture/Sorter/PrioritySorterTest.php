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

use Doctrine\Fixture\Sorter\PrioritySorter;
use Doctrine\Test\Mock;

/**
 * PrioritySorter tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class PrioritySorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Fixture\Sorter\PrioritySorter
     */
    private $sorter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->sorter = new PrioritySorter();
    }

    public function testSuccessLinearOrdering()
    {
        $node1 = new Mock\Node(1);
        $node2 = new Mock\Node(2);
        $node3 = new Mock\Node(3);
        $node4 = new Mock\Node(4);
        $node5 = new Mock\Node(5);

        $this->sorter->insert($node1, 2);
        $this->sorter->insert($node2, 3);
        $this->sorter->insert($node3, 4);
        $this->sorter->insert($node4, 5);
        $this->sorter->insert($node5, 1);

        $sortedList  = $this->sorter->sort();
        $correctList = array($node5, $node1, $node2, $node3, $node4);

        $this->assertSame($sortedList, $correctList);
    }

    public function testSuccessCollisionOrdering()
    {
        $node1 = new Mock\Node(1);
        $node2 = new Mock\Node(2);
        $node3 = new Mock\Node(3);
        $node4 = new Mock\Node(4);
        $node5 = new Mock\Node(5);

        $this->sorter->insert($node1, 2);
        $this->sorter->insert($node2, 1);
        $this->sorter->insert($node3, 0);
        $this->sorter->insert($node4, 1);
        $this->sorter->insert($node5, 0);

        $sortedList  = $this->sorter->sort();
        $correctList = array($node5, $node3, $node4, $node2, $node1);

        $this->assertSame($sortedList, $correctList);
    }
}
