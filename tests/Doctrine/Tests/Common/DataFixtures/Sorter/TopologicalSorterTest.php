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

namespace Doctrine\Test\DataFixtures\Sorter;

use Doctrine\Common\DataFixtures\Exception\CircularReferenceException;
use Doctrine\Common\DataFixtures\Sorter\TopologicalSorter;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * TopologicalSorter tests.
 *
 * Note: When writing tests here consider that a lot of graph
 *       constellations can have many valid orderings, so you may want to
 *       build a graph that has only 1 valid order to simplify your tests
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 *
 * @covers \Doctrine\Common\DataFixtures\Sorter\TopologicalSorter
 */
class TopologicalSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Common\DataFixtures\Sorter\TopologicalSorter
     */
    private $sorter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->sorter = new TopologicalSorter();
    }

    public function testSuccessSortLinearDependency()
    {
        $node1 = new ClassMetadata(1);
        $node2 = new ClassMetadata(2);
        $node3 = new ClassMetadata(3);
        $node4 = new ClassMetadata(4);
        $node5 = new ClassMetadata(5);

        $this->sorter->addNode('1', $node1);
        $this->sorter->addNode('2', $node2);
        $this->sorter->addNode('3', $node3);
        $this->sorter->addNode('4', $node4);
        $this->sorter->addNode('5', $node5);

        $this->sorter->addDependency('1', '2');
        $this->sorter->addDependency('2', '3');
        $this->sorter->addDependency('3', '4');
        $this->sorter->addDependency('5', '1');

        $sortedList  = $this->sorter->sort();
        $correctList = array($node4, $node3, $node2, $node1, $node5);

        self::assertSame($correctList, $sortedList);
    }

    public function testSuccessSortMultiDependency()
    {
        $node1 = new ClassMetadata(1);
        $node2 = new ClassMetadata(2);
        $node3 = new ClassMetadata(3);
        $node4 = new ClassMetadata(4);
        $node5 = new ClassMetadata(5);

        $this->sorter->addNode('1', $node1);
        $this->sorter->addNode('2', $node2);
        $this->sorter->addNode('3', $node3);
        $this->sorter->addNode('4', $node4);
        $this->sorter->addNode('5', $node5);

        $this->sorter->addDependency('3', '2');
        $this->sorter->addDependency('3', '4');
        $this->sorter->addDependency('3', '5');
        $this->sorter->addDependency('4', '1');
        $this->sorter->addDependency('5', '1');

        $sortedList  = $this->sorter->sort();
        $correctList = array($node1, $node2, $node4, $node5, $node3);

        self::assertSame($correctList, $sortedList);
    }

    public function testFailureSortCyclicDependency()
    {
        $node1 = new ClassMetadata(1);
        $node2 = new ClassMetadata(2);
        $node3 = new ClassMetadata(3);

        $this->sorter->addNode('1', $node1);
        $this->sorter->addNode('2', $node2);
        $this->sorter->addNode('3', $node3);

        $this->sorter->addDependency('1', '2');
        $this->sorter->addDependency('2', '3');
        $this->sorter->addDependency('3', '1');

        $this->expectException(CircularReferenceException::class);

        $this->sorter->sort();
    }

    public function testNoFailureOnSelfReferencingDependency()
    {
        $node1 = new ClassMetadata(1);
        $node2 = new ClassMetadata(2);
        $node3 = new ClassMetadata(3);
        $node4 = new ClassMetadata(4);
        $node5 = new ClassMetadata(5);

        $this->sorter->addNode('1', $node1);
        $this->sorter->addNode('2', $node2);
        $this->sorter->addNode('3', $node3);
        $this->sorter->addNode('4', $node4);
        $this->sorter->addNode('5', $node5);

        $this->sorter->addDependency('1', '2');
        $this->sorter->addDependency('1', '1');
        $this->sorter->addDependency('2', '3');
        $this->sorter->addDependency('3', '4');
        $this->sorter->addDependency('5', '1');

        $sortedList  = $this->sorter->sort();
        $correctList = array($node4, $node3, $node2, $node1, $node5);

        self::assertSame($correctList, $sortedList);
    }

    public function testNoOverrideExistingNodeDependency()
    {
        $node1 = new ClassMetadata(1);
        $node2 = new ClassMetadata(2);

        $this->sorter->addNode('1', $node1);
        $this->sorter->addNode('2', $node2);
        $this->sorter->addDependency('1', '2');
        $this->sorter->addNode('1', $node1);

        $sortedList  = $this->sorter->sort();
        $correctList = array($node2, $node1);
        $this->assertSame($correctList, $sortedList);
    }

    public function testCannotAddDependencyForAMissingNodeTo()
    {
        $node1 = new ClassMetadata(1);

        $this->sorter->addNode('1', $node1);

        $this->expectException(\RuntimeException::class);
        $this->sorter->addDependency('1', '2');
    }

    public function testCannotAddDependencyForAMissingNodeFrom()
    {
        $node2 = new ClassMetadata(2);

        $this->sorter->addNode('2', $node2);

        $this->expectException(\RuntimeException::class);
        $this->sorter->addDependency('1', '2');
    }

    public function testCannotAddDependencyForAMissingNodes()
    {
        $this->expectException(\RuntimeException::class);
        $this->sorter->addDependency('1', '2');
    }
}
