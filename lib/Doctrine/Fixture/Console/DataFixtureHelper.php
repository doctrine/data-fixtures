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

use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Loader\Loader;
use Symfony\Component\Console\Helper\Helper;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class DataFixtureHelper extends Helper
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
     * Class constructor
     *
     * @param Configuration $configuration
     * @param Loader $loader
     * @param ChainFilter $filter
     */
    public function __construct(
        Configuration $configuration,
        Loader $loader,
        ChainFilter $filter = null
    ) {
        $this->configuration = $configuration;
        $this->loader = $loader;
        $this->filter = $filter ?: new ChainFilter();
    }

    /**
     * Retrieve the fixture loader.
     *
     * @return Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the fixture filter.
     *
     * @return ChainFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Retrieve the configuration.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'data-fixtures';
    }
}
