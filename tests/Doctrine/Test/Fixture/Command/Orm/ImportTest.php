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

namespace Doctrine\Test\Fixture\Command\Orm;

use Doctrine\Fixture\Executor;
use Doctrine\Fixture\Command\Orm\Import;

/**
 * Import tests.
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigureShouldSetTheBasicCommandData()
    {
        $command  = new Import();
        $method   = new \ReflectionMethod($command, 'configure');
        $property = new \ReflectionProperty('Symfony\Component\Console\Command\Command', 'definition');

        $property->setAccessible(true);
        $method->setAccessible(true);
        $method->invoke($command);

        $this->assertAttributeEquals(Executor::IMPORT, 'executionFlags', $command);
        $this->assertAttributeEquals('orm:fixtures:import', 'name', $command);
        $this->assertTrue($property->getValue($command)->hasOption('group'));
    }
}
