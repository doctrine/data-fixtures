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

namespace Doctrine\Common\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

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
     * List of identifiers stored for references
     * in case if reference gets unmanaged, it will
     * use a proxy referenced by this identity
     *
     * @var array
     */
    private $identities = array();

    /**
     * Currently used object manager
     *
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * Initialize the ReferenceRepository
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get identifier for a unit of work
     *
     * @param object $reference Reference object
     * @param object $uow       Unit of work
     *
     * @return mixed
     */
    protected function getIdentifier($reference, $uow)
    {
        if (method_exists($uow, 'getEntityIdentifier')) {
            return $uow->getEntityIdentifier($reference);
        }

        return $uow->getDocumentIdentifier($reference);
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to $reference. If $name
     * already is set, it overrides it
     *
     * @param string $name
     * @param object $reference
     */
    public function setReference($name, $reference)
    {
        $this->references[$name] = $reference;
        // in case if reference is set after flush, store its identity
        $uow = $this->manager->getUnitOfWork();
        if ($uow->isInIdentityMap($reference)) {
            $this->identities[$name] = $this->getIdentifier($reference, $uow);
        }
    }

    /**
     * Store the identifier of a reference
     *
     * @param string $name
     * @param mixed $identity
     */
    public function setReferenceIdentity($name, $identity)
    {
        $this->identities[$name] = $identity;
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
     *      a reference by $name
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
        $reference = $this->references[$name];
        $meta = $this->manager->getClassMetadata(get_class($reference));
        $uow = $this->manager->getUnitOfWork();
        if (!$uow->isInIdentityMap($reference) && isset($this->identities[$name])) {
            $reference = $this->manager->getReference(
                $meta->name,
                $this->identities[$name]
            );
            $this->references[$name] = $reference; // already in identity map
        }
        return $reference;
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @param string $name
     * @return boolean
     */
    public function hasReference($name)
    {
        return isset($this->references[$name]);
    }

    /**
     * Searches for a reference name in the
     * list of stored references
     *
     * @param object $reference
     * @return string
     */
    public function getReferenceName($reference)
    {
        return array_search($reference, $this->references, true);
    }

    /**
     * Checks if reference has identity stored
     *
     * @param string $name
     */
    public function hasIdentity($name)
    {
        return array_key_exists($name, $this->identities);
    }

    /**
     * Get all stored identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
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

    /**
     * Get object manager
     *
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager()
    {
        return $this->manager;
    }
}
