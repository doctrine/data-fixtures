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

namespace Doctrine\ORM\DataFixtures;

use Doctrine\ORM\EntityManager;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Executor
{
    /** Purger instance for purging database before loading data fixtures */
    private $purger;

    /**
     * Construct new fixtures loader instance.
     *
     * @param EntityManager $em EntityManager instance used for persistence.
     */
    public function __construct(EntityManager $em, Purger $purger = null)
    {
        $this->em = $em;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setEntityManager($em);
        }
    }

    /**
     * Sets the Purger instance to use for this exector instance.
     *
     * @param Purger $purger
     */
    public function setPurger(Purger $purger)
    {
        $this->purger = $purger;
    }

    /**
     * Executes the given array of data fixtures.
     *
     * @param array $fixtures Array of fixtures to execute.
     * @param boolean $append Whether to append the data fixtures or purge the database before loading.
     */
    public function execute(array $fixtures, $append = false)
    {
        $purger = $this->purger;
        $this->em->transactional(function(EntityManager $em) use ($fixtures, $append, $purger) {
            if ($append === false) {
                if ($purger === null) {
                    throw new \Exception('Doctrine\ORM\DataFixtures\Purger instance is required if you want to purge the database before loading your data fixtures.');
                }
                $purger->purge();
            }

            foreach ($fixtures as $fixture) {
                $fixture->load($em);
            }
        });
    }
}