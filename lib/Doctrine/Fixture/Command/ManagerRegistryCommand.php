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
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Fixture\Persistence\ManagerRegistryEventSubscriber;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ManagerRegistryCommand extends AbstractCommand
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * Configures the manager registry.
     *
     * @param ManagerRegistry $managerRegistry
     * @return ManagerRegistryCommand
     */
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateEventManager(EventManager $eventManager)
    {
        if ($this->managerRegistry === null) {
            throw new \RuntimeException('The ManagerRegistry should be configured!');
        }

        $eventManager->addEventSubscriber(
            new ManagerRegistryEventSubscriber($this->managerRegistry)
        );
    }
}
