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

namespace Doctrine\Fixture;

use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Event\BulkFixtureEvent;
use Doctrine\Fixture\Event\BulkImportFixtureEventListener;
use Doctrine\Fixture\Event\BulkPurgeFixtureEventListener;
use Doctrine\Fixture\Filter\Filter;
use Doctrine\Fixture\Loader\Loader;

/**
 * Executor class, responsible to import/purge fixtures.
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class Executor
{
    const PURGE  = 1;
    const IMPORT = 2;

    /**
     * @var \Doctrine\Fixture\Configuration
     */
    private $configuration;

    /**
     * Constructor.
     *
     * @param \Doctrine\Fixture\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;

        // Adding default bulk executor
        $eventManager = $this->configuration->getEventManager();

        $eventManager->addEventSubscriber(new Executor\BulkExecutorEventSubscriber());
    }

    /**
     * Execute importing process.
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     * @param \Doctrine\Fixture\Filter\Filter $filter
     * @param integer                         $flags
     */
    public function execute(Loader $loader, Filter $filter, $flags = self::IMPORT)
    {
        $eventManager = $this->configuration->getEventManager();
        $fixtureList  = $this->getFixtureList($loader, $filter);

        if ($flags & self::PURGE) {
            // Purging needs to happen in reverse order of execution
            $event = new BulkFixtureEvent($this->configuration, array_reverse($fixtureList));

            $eventManager->dispatchEvent(BulkPurgeFixtureEventListener::BULK_PURGE, $event);
        }

        if ($flags & self::IMPORT) {
            $event = new BulkFixtureEvent($this->configuration, $fixtureList);

            $eventManager->dispatchEvent(BulkImportFixtureEventListener::BULK_IMPORT, $event);
        }
    }

    /**
     * Filter and calculate the order of fixtures for execution.
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     * @param \Doctrine\Fixture\Filter\Filter $filter
     *
     * @return array<\Doctrine\Fixture\Fixture>
     */
    private function getFixtureList(Loader $loader, Filter $filter)
    {
        $calculatorFactory = $this->configuration->getCalculatorFactory();
        $fixtureList       = array_filter(
            $loader->load(),
            function ($fixture) use ($filter) {
                return $filter->accept($fixture);
            }
        );

        $calculator = $calculatorFactory->getCalculator($fixtureList);

        return $calculator->calculate($fixtureList);
    }
}
