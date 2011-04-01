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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\OrderedByParentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;

require_once __DIR__.'/TestInit.php';

/**
 * Test Fixture ordering.
 *
 * @author Gustavo Adrian <comfortablynumb84@gmail.com>
 */
class OrderedByParentFixtureTest extends BaseTest
{
    public function test_orderFixturesByParentClass_orderClassesWithASingleParent()
    {
        $loader = new Loader();
        $loader->addFixture(new OrderedByParentFixture3);
        $loader->addFixture(new OrderedByParentFixture1);
        $loader->addFixture(new OrderedByParentFixture2);
        $loader->addFixture(new BaseParentFixture1);

        $orderedFixtures = $loader->getFixtures();

        $this->assertEquals(4, count($orderedFixtures));

        $this->assertTrue(array_shift($orderedFixtures) instanceof BaseParentFixture1);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture1);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture2);
        $this->assertTrue(array_shift($orderedFixtures) instanceof OrderedByParentFixture3);
    }

    public function test_orderFixturesByParentClass_orderClassesWithAMultipleParents()
    {
        $loader = new Loader();

        $addressFixture         = new AddressFixture();
        $contactMethodFixture   = new ContactMethodFixture();
        $contactFixture         = new ContactFixture();
        $baseParentFixture      = new BaseParentFixture1();
        $countryFixture         = new CountryFixture();
        $stateFixture           = new StateFixture();

        $loader->addFixture( $addressFixture );
        $loader->addFixture( $contactMethodFixture );
        $loader->addFixture( $contactFixture );
        $loader->addFixture( $baseParentFixture );
        $loader->addFixture( $countryFixture );
        $loader->addFixture( $stateFixture );

        $orderedFixtures = $loader->getFixtures();

        $this->assertEquals(6, count($orderedFixtures));

        $contactFixtureOrder        = array_search( $contactFixture, $orderedFixtures );
        $contactMethodFixtureOrder  = array_search( $contactMethodFixture, $orderedFixtures );
        $addressFixtureOrder        = array_search( $addressFixture, $orderedFixtures );
        $countryFixtureOrder        = array_search( $countryFixture, $orderedFixtures );
        $stateFixtureOrder          = array_search( $stateFixture, $orderedFixtures );
        $baseParentFixtureOrder     = array_search( $baseParentFixture, $orderedFixtures );
        
        // Order of fixtures is not exact. We need to test, however, that dependencies are
        // indeed satisfied
        
        // BaseParentFixture1 has no dependencies, so it will always be first in this case
        $this->assertEquals( $baseParentFixtureOrder, 0 );

        $this->assertTrue( ( $contactFixtureOrder > $contactMethodFixtureOrder ) );
        $this->assertTrue( ( $contactFixtureOrder > $addressFixtureOrder ) );
        $this->assertTrue( ( $contactFixtureOrder > $countryFixtureOrder ) );
        $this->assertTrue( ( $contactFixtureOrder > $stateFixtureOrder ) );
        $this->assertTrue( ( $contactFixtureOrder > $contactMethodFixtureOrder ) );

        $this->assertTrue( ( $addressFixtureOrder > $stateFixtureOrder ) );
        $this->assertTrue( ( $addressFixtureOrder > $countryFixtureOrder ) );
    }


    /**
     * @expectedException Doctrine\Common\DataFixtures\Exception\CircularReferenceException
     */
    public function test_orderFixturesByParentClass_circularReferencesMakeMethodThrowCircularReferenceException()
    {
        $loader = new Loader();
        
        $loader->addFixture( new CircularReferenceFixture3 );
        $loader->addFixture( new CircularReferenceFixture );
        $loader->addFixture( new CircularReferenceFixture2 );

        $orderedFixtures = $loader->getFixtures();
    }
}

class OrderedByParentFixture1 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1' );
    }
}

class OrderedByParentFixture2 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 'Doctrine\Tests\Common\DataFixtures\OrderedByParentFixture1' );
    }
}

class OrderedByParentFixture3 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 'Doctrine\Tests\Common\DataFixtures\OrderedByParentFixture2' );
    }
}

class BaseParentFixture1 implements FixtureInterface
{
    public function load($manager)
    {}
}

class CountryFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1'
        );
    }
}

class StateFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1',
            'Doctrine\Tests\Common\DataFixtures\CountryFixture'
        );
    }
}

class AddressFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1',
            'Doctrine\Tests\Common\DataFixtures\CountryFixture',
            'Doctrine\Tests\Common\DataFixtures\StateFixture'
        );
    }
}

class ContactMethodFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\BaseParentFixture1'
        );
    }
}

class ContactFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\AddressFixture',
            'Doctrine\Tests\Common\DataFixtures\ContactMethodFixture'
        );
    }
}

class CircularReferenceFixture implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\CircularReferenceFixture3'
        );
    }
}

class CircularReferenceFixture2 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\CircularReferenceFixture'
        );
    }
}

class CircularReferenceFixture3 implements FixtureInterface, OrderedByParentFixtureInterface
{
    public function load($manager)
    {}

    public function getParentDataFixtureClasses()
    {
        return array( 
            'Doctrine\Tests\Common\DataFixtures\CircularReferenceFixture2'
        );
    }
}
