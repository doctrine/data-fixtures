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

namespace Doctrine\Test\Fixture\Filter;

use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Filter\GroupedFilter;
use Doctrine\Test\Mock\Unassigned\FixtureA as UnassignedFixtureA;
use Doctrine\Test\Mock\Grouped;

/**
 * ChainFilter tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ChainFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $unassignedFixtureA = new UnassignedFixtureA();
        $groupedFixtureA    = new Grouped\FixtureA();
        $groupedFixtureB    = new Grouped\FixtureB();
        $filter             = new ChainFilter(array(
            new GroupedFilter(array('test')),
        ));

        $this->assertTrue($filter->accept($unassignedFixtureA));
        $this->assertTrue($filter->accept($groupedFixtureA));
        $this->assertFalse($filter->accept($groupedFixtureB));
    }

    public function testGetFilterList()
    {
        $filter = new ChainFilter(array(
            new GroupedFilter(array('test')),
        ));

        $filterList = $filter->getFilterList();

        $this->assertCount(1, $filterList);
        $this->assertInstanceOf('Doctrine\Fixture\Filter\GroupedFilter', reset($filterList));
    }

    public function testAddFilter()
    {
        $filter = new ChainFilter(array(
            new GroupedFilter(array('test')),
        ));

        $this->assertCount(1, $filter->getFilterList());

        $filter->addFilter(new GroupedFilter(array('another_test')));

        $this->assertCount(2, $filter->getFilterList());
    }

    public function testRemoveFilter()
    {
        $testFilter        = new GroupedFilter(array('test'));
        $anotherTestFilter = new GroupedFilter(array('another_test'));

        $filter = new ChainFilter(array(
            $testFilter,
            $anotherTestFilter,
        ));

        $this->assertCount(2, $filter->getFilterList());

        $filter->removeFilter($testFilter);

        $this->assertCount(1, $filter->getFilterList());
    }
}
