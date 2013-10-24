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

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class MongoDBPurger implements PurgerInterface
{
    /** DocumentManager instance used for persistence. */
    private $dm;

    /**
     * Construct new purger instance.
     *
     * @param DocumentManager $dm DocumentManager instance used for persistence.
     */
    public function __construct(DocumentManager $dm = null)
    {
        $this->dm = $dm;
    }

    /**
     * Set the DocumentManager instance this purger instance should use.
     *
     * @param DocumentManager $dm
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Retrieve the DocumentManager instance this purger instance is using.
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getObjectManager()
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function purge()
    {
        $metadatas = $this->dm->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            if ( ! $metadata->isMappedSuperclass) {
                $this->dm->getDocumentCollection($metadata->name)->drop();
            }
        }
        $this->dm->getSchemaManager()->ensureIndexes();
    }
}
