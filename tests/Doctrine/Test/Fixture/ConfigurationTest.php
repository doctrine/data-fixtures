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

namespace Doctrine\Test\Fixture;

use Doctrine\Common\EventManager;
use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Filter\ChainFilter;
use Doctrine\Fixture\Sorter\CalculatorFactory;

/**
 * Configuration tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testEventManager()
    {
        $configuration = new Configuration();
        $eventManager  = new EventManager();

        $configuration->setEventManager($eventManager);

        $this->assertEquals($eventManager, $configuration->getEventManager());
    }

    public function testFilter()
    {
        $configuration = new Configuration();
        $filter        = new ChainFilter();

        $configuration->setFilter($filter);

        $this->assertEquals($filter, $configuration->getFilter());
    }

    public function testCalculatorFactory()
    {
        $configuration     = new Configuration();
        $calculatorFactory = new CalculatorFactory();

        $configuration->setCalculatorFactory($calculatorFactory);

        $this->assertEquals($calculatorFactory, $configuration->getCalculatorFactory());
    }
}