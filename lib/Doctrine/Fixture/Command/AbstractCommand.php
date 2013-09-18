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

namespace Doctrine\Fixture\Command;

use Doctrine\Common\EventManager;
use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Filter\GroupedFilter;
use Doctrine\Fixture\Loader\Loader;
use Doctrine\Fixture\Sorter\CalculatorFactory;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Base class for data fixture commands.
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var ChainFilter
     */
    protected $filter;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var integer
     */
    protected $executionFlags;

    /**
     * Retrieve the fixture loader.
     *
     * @return Loader
     */
    protected function getLoader()
    {
        return $this->loader;
    }

    /**
     * Configures the fixture loader.
     *
     * @param Loader $loader
     *
     * @return AbstractCommand
     */
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Retrieve the fixture filter.
     *
     * @return ChainFilter
     */
    protected function getFilter()
    {
        if ($this->filter === null) {
            $this->filter = new ChainFilter();
        }

        return $this->filter;
    }

    /**
     * Configures the fixture filter.
     *
     * @param ChainFilter $filter
     *
     * @return AbstractCommand
     */
    public function setFilter(ChainFilter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Configures the flags that should be used on the execution.
     *
     * @param integer $executionFlags
     *
     * @return AbstractCommand
     */
    protected function setExecutionFlags($executionFlags)
    {
        $this->executionFlags = $executionFlags;

        return $this;
    }

    /**
     * Retrieve the configuration.
     *
     * @return Configuration
     */
    protected function getConfiguration()
    {
        if ($this->configuration === null) {
            $this->configuration = new Configuration();

            $this->configuration->setEventManager(new EventManager());
            $this->configuration->setCalculatorFactory(new CalculatorFactory());
        }

        return $this->configuration;
    }

    /**
     * Configures the configuration.
     *
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->loader === null) {
            throw new \RuntimeException('You must provide a fixture loader!');
        }

        $configuration = $this->getConfiguration();

        $this->updateFilter($input);
        $this->updateEventManager($configuration->getEventManager());

        $executor = new Executor($configuration);
        $executor->execute($this->loader, $this->getFilter(), $this->executionFlags);
    }

    /**
     * Update filter configuration based on provided input.
     *
     * @param InputInterface $input
     */
    protected function updateFilter(InputInterface $input)
    {
        if (($groupList = $input->getOption('group')) !== null) {
            $this->getFilter()->addFilter(new GroupedFilter($groupList, true));
        }
    }

    /**
     * Register the subscribers on the event manager.
     *
     * @param EventManager $eventManager
     */
    abstract protected function updateEventManager(EventManager $eventManager);
}
