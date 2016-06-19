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

namespace Doctrine\Tests\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Tests\Common\DataFixtures\BaseTest;

/**
 * Test Fixture executor.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ORMExecutorTest extends BaseTest
{
    public function testExecuteWithNoPurge()
    {
        $em = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('setEntityManager')
            ->with($em);
        $executor = new ORMExecutor($em, $purger);
        $fixture = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute(array($fixture), true);
    }

    public function testExecuteWithPurge()
    {
        $em = $this->getMockSqliteEntityManager();
        $purger = $this->getMockPurger();
        $purger->expects($this->once())
            ->method('purge')
            ->will($this->returnValue(null));
        $executor = new ORMExecutor($em, $purger);
        $fixture = $this->getMockFixture();
        $fixture->expects($this->once())
            ->method('load')
            ->with($em);
        $executor->execute(array($fixture), false);
    }

    public function testExecuteTransaction()
    {
        $em = $this->getMockSqliteEntityManager();
        $executor = new ORMExecutor($em);
        $fixture = $this->getMockFixture();
        $executor->execute(array($fixture), true);
    }

    private function getMockFixture()
    {
        return $this->createMock(FixtureInterface::class);
    }

    private function getMockPurger()
    {
        return $this->createMock(ORMPurger::class);
    }
}
