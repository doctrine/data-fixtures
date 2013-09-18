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

namespace Doctrine\Fixture\Console;

use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Filter\GroupedFilter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for data fixture commands.
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $helper DataFixtureHelper */
        $helper        = $this->getHelper('data-fixtures');
        $configuration = $helper->getConfiguration();
        $loader        = $helper->getLoader();
        $filter        = $helper->getFilter();
        $flags         = $helper->getExecutionFlags();

        $this->updateFilter($input);

        $executor = new Executor($configuration);
        $executor->execute($loader, $filter, $flags);
    }

    /**
     * Update filter configuration based on provided input.
     *
     * @param InputInterface $input
     */
    protected function updateFilter(InputInterface $input)
    {
        /** @var $helper DataFixtureHelper */
        $helper = $this->getHelper('data-fixtures');
        $filter = $helper->getFilter();

        if (($groupList = $input->getOption('group')) !== null) {
            $filter->addFilter(new GroupedFilter($groupList, true));
        }
    }
}
