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
 * Grouped Filter allows you to restrict the fixtures to be loaded by matching
 * against a set of provided allowed groups for execution.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class GroupedFilter implements Filter
{
    /**
     * @var array<string>
     */
    private $allowedGroupList;

    /**
     * @var boolean
     */
    private $onlyImplementors;

    /**
     * Constructor.
     *
     * @param array<string> $allowedGroupList
     * @param boolean       $onlyImplementors
     */
    public function __construct(array $allowedGroupList, $onlyImplementors = false)
    {
        $this->allowedGroupList = $acceptedGroupList;
        $this->onlyImplementors = $onlyImplementors;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Fixture $fixture)
    {
        if ( ! ($fixture instanceof GroupedFixture)) {
            return ( ! $this->onlyImplementors);
        }

        $matchingGroupList = array_intersect($fixture->getGroupList(), $this->allowedGroupList);

        return (count($matchingGroupList) === 0);
    }
}