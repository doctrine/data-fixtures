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

namespace Doctrine\Test\Fixture\Event;

use Doctrine\Fixture\Configuration;
use Doctrine\Fixture\Event\BulkFixtureEvent;
use Doctrine\Test\Mock\Unassigned;

/**
 * BulkFixtureEvent tests.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class BulkFixtureEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $configuration = new Configuration();
        $fixtureA      = new Unassigned\FixtureA();
        $event         = new BulkFixtureEvent($configuration, array($fixtureA));

        $this->assertEquals($configuration, $event->getConfiguration());
        $this->assertEquals(array($fixtureA), $event->getFixtureList());
    }
}