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

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\Search\SearchManager;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class SearchPurger implements PurgerInterface
{
    /** SearchManager instance used for persistence. */
    private $sm;

    /**
     * Construct new purger instance.
     *
     * @param SearchManager $sm SearchManager instance used for persistence.
     */
    public function __construct(SearchManager $sm = null)
    {
        $this->sm = $sm;
    }

    /**
     * Set the SearchManager instance this purger instance should use.
     *
     * @param SearchManager $dm
     */
    public function setSearchManager(SearchManager $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Retrieve the SearchManager instance this purger instance is using.
     *
     * @return \Doctrine\Search\SearchManager
     */
    public function getSearchManager()
    {
        return $this->sm;
    }

    /** 
     * {@inheritDoc}
     */
    public function purge()
    {
        $metadatas = $this->sm->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            $this->sm->getClient()->removeAll($metadata->index, $metadata->type);
        }
    }
}
