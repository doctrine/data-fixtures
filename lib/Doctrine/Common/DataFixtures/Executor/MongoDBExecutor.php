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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Event\Listener\MongoDBReferenceListener;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class MongoDBExecutor extends AbstractExecutor
{
    /**
     * Construct new fixtures loader instance.
     *
     * @param DocumentManager $dm DocumentManager instance used for persistence.
     */
    public function __construct(DocumentManager $dm, MongoDBPurger $purger = null)
    {
        $this->dm = $dm;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setDocumentManager($dm);
        }
        parent::__construct($dm);
        $dm->getEventManager()->addEventSubscriber(
            new MongoDBReferenceListener($this->referenceRepository)
        );
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        if ($append === false) {
            $this->purge();
        }
        foreach ($fixtures as $fixture) {
            $this->load($this->dm, $fixture);
        }
    }
}