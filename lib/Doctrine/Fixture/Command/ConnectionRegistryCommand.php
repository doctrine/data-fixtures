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
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Fixture\Persistence\ConnectionRegistryEventSubscriber;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ConnectionRegistryCommand extends AbstractCommand
{
    /**
     * @var ConnectionRegistry
     */
    protected $connectionRegistry;

    /**
     * Configures the connection registry.
     *
     * @param ConnectionRegistry $managerRegistry
     * @return ConnectionRegistryCommand
     */
    public function setConnectionRegistry(ConnectionRegistry $managerRegistry)
    {
        $this->connectionRegistry = $managerRegistry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateEventManager(EventManager $eventManager)
    {
        if ($this->connectionRegistry === null) {
            throw new \RuntimeException('The ConnectionRegistry should be configured!');
        }

        $eventManager->addEventSubscriber(
            new ConnectionRegistryEventSubscriber($this->connectionRegistry)
        );
    }
}
