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

namespace Doctrine\Fixture\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Fixture\Configuration;

/**
 * A generic Bulk Fixture event.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class BulkFixtureEvent extends EventArgs
{
    /**
     * @var \Doctrine\Fixture\Configuration
     */
    private $configuration;

    /**
     * @var array<Doctrine\Fixture\Fixture>
     */
    private $fixtureList;

    /**
     * Constructor.
     *
     * @param array<Doctrine\Fixture\Fixture> $fixtureList
     */
    public function __construct(Configuration $configuration, array $fixtureList)
    {
        $this->configuration = $configuration;
        $this->fixtureList   = $fixtureList;
    }

    /**
     * Retrieve the Configuration.
     *
     * @return \Doctrine\Fixture\Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Retrieve the fixture associated to event.
     *
     * @return array<Doctrine\Fixture\Fixture>
     */
    public function getFixtureList()
    {
        return $this->fixtureList;
    }
}