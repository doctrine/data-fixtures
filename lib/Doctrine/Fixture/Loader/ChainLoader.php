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

namespace Doctrine\Fixture\Loader;

/**
 * ChainLoader loads a list of fixtures based on a chain of loaders.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ChainLoader implements Loader
{
    /**
     * @var array<Doctrine\Fixture\Loader\Loader>
     */
    private $loaderList;

    /**
     * Constructor.
     *
     * @param array $loaderList
     */
    public function __construct(array $loaderList = array())
    {
        $this->loaderList = $loaderList;
    }

    /**
     * Retrieve the list of loaders.
     *
     * @return array
     */
    public function getLoaderList()
    {
        return $this->loaderList;
    }

    /**
     * Add a new loader to the list.
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     */
    public function addLoader(Loader $loader)
    {
        $this->loaderList[] = $loader;
    }

    /**
     * Removes a loader from the list.
     *
     * {@internal Unable to use array_diff since PHP is only able to handle
     *            string comparisons.}
     *
     * @param \Doctrine\Fixture\Loader\Loader $loader
     */
    public function removeLoader(Loader $loader)
    {
        $this->loaderList = array_filter(
            $this->loaderList,
            function ($loaderElement) use ($loader)
            {
                return ($loaderElement !== $loader);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $fixtureList = array();

        foreach ($this->loaderList as $loader) {
            $loaderFixtureList = $loader->load();

            $fixtureList = array_merge($fixtureList, $loaderFixtureList);
        }

        return $fixtureList;
    }
}
