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

namespace Doctrine\Common\DataFixtures;

/**
 * ReferenceRepository class manages references for
 * fixtures in order to easily support the relations
 * between fixtures
 * 
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class ReferenceRepository
{
    /**
     * List of named references to the fixture objects
     * gathered during loads of fixtures
     * 
     * @var array
     */
    private $references = array();
    
    /**
     * Currently used object manager
     * 
     * @var object - object manager
     */
    private $manager;
    
    /**
     * Initialize the ReferenceRepository
     * 
     * @param object $manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }
    
    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * already is set, it overrides it
     *
     * @param string $name
     * @param object $object - managed object
     * @throws LogicException - if object is not mapped or
     * 		does not have identifier yet
     * @return void
     */
    public function setReference($name, $object)
    {
        $this->references[$name] = $object;
    }
    
    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. $name must
     * not be set yet
     * 
     * Notice: in case if identifier is generated after
     * the record is inserted, be sure tu use this method
     * after $object is flushed
     * 
     * @param string $name
     * @param object $object - managed object
     * @throws BadMethodCallException - if repository already has
     * 		a reference by $name
     * @return void
     */
    public function addReference($name, $object)
    {
        if (isset($this->references[$name])) {
            throw new \BadMethodCallException("Reference to: ({$name}) already exists, use method setReference in order to override it");
        }
        $this->setReference($name, $object);
    }
    
    /**
     * Loads an object using stored reference
     * named by $name
     * 
     * @param string $name
     * @return object
     */
    public function getReference($name)
    {
        return $this->references[$name];
    }
    
    /**
     * Get all stored references
     * 
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}