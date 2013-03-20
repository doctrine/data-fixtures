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

namespace Doctrine\Fixture\Filter;

use Doctrine\Fixture\Fixture;

/**
 * ChainLoader provides a composition of filters to be applied over a fixture.
 * Only if all filters accept the fixture it will be loaded. If any filter in
 * the chain fails, it will be removed from execution list.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ChainFilter implements Filter
{
    /**
     * @var array<Doctrine\Fixture\Filter\Filter>
     */
    private $filterList;

    /**
     * Constructor.
     *
     * @param array $filterList
     */
    public function __construct(array $filterList = array())
    {
        $this->filterList = $filterList;
    }

    /**
     * Retrieve the list of filters.
     *
     * @return array
     */
    public function getFilterList()
    {
        return $this->filterList;
    }

    /**
     * Add a new filter to the list.
     *
     * @param \Doctrine\Fixture\Filter\Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $this->filterList[] = $filter;
    }

    /**
     * Removes a filter from the list.
     *
     * {@internal Unable to use array_diff since PHP is only able to handle
     *            string comparisons.}
     *
     * @param \Doctrine\Fixture\Filter\Filter $filter
     */
    public function removeFilter(Filter $filter)
    {
        $this->filterList = array_filter(
            $this->filterList,
            function ($filterElement) use ($filter)
            {
                return ($filterElement !== $filter);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Fixture $fixture)
    {
        foreach ($this->filterList as $filter) {
            if ( ! $filter->accept($fixture)) {
                return false;
            }
        }

        return true;
    }
}