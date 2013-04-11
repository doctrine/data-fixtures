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

use Doctrine\Fixture\Filter\Filter;
use Doctrine\Fixture\Loader\Loader;
use Doctrine\Fixture\Configuration;

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
        $fixtureList = $this->getFixtureList($loader, $filter);

        if ($flags & self::PURGE) {
            // Purging needs to happen in reverse order of execution
            $this->purgeFixtureList(array_reverse($fixtureList));
        }

        if ($flags & self::IMPORT) {
            $this->importFixtureList($fixtureList);
        }
    }

    /**
     * Filter and calculate the order of fixtures for execution.
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     * @param \Doctrine\Fixture\Filter\Filter $filter
     *
     * @return array<Doctrine\Fixture\Fixture>
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

    /**
     * Purges the fixtures.
     *
     * @param array<Doctrine\Fixture\Fixture> $fixtureList
     *
     * @return void
     */
    private function purgeFixtureList(array $fixtureList)
    {
        $eventManager = $this->configuration->getEventManager();

        foreach ($fixtureList as $fixture) {
            $eventManager->dispatchEvent('purge', new Event\FixtureEvent($fixture));

            $fixture->purge();
        }
    }

    /**
     * Imports the fixtures.
     *
     * @param array<Doctrine\Fixture\Fixture> $fixtureList
     *
     * @return void
     */
    private function importFixtureList(array $fixtureList)
    {
        $eventManager = $this->configuration->getEventManager();

        foreach ($fixtureList as $fixture) {
            $eventManager->dispatchEvent('import', new Event\FixtureEvent($fixture));

            $fixture->import();
        }
    }
}
