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

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Search\SearchManager;
use Doctrine\Common\DataFixtures\Purger\SearchPurger;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class SearchExecutor extends AbstractExecutor
{
    /**
     * Construct new fixtures loader instance.
     *
     * @param SearchManager $sm ObjectManager instance used for persistence.
     * @param SearchPurger $purger PurgerInterface instance used for purging database
     */
    public function __construct(SearchManager $sm, SearchPurger $purger = null)
    {
        $this->sm = $sm;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setSearchManager($sm);
        }
        parent::__construct($sm);
        //TODO: implement reference repository and listener
    }

    /**
     * Retrieve the EntityManager instance this executor instance is using.
     *
     * @return Doctrine\Search\SearchManager
     */
    public function getSearchManager()
    {
        return $this->sm;
    }

    /** 
     * {@inheritDoc} 
     */
    public function execute(array $fixtures, $append = false)
    {
        if ($append === false) {
            $this->purger->purge();
        }
        foreach ($fixtures as $fixture) {
            $this->load($this->sm, $fixture);
        }
    }
}
