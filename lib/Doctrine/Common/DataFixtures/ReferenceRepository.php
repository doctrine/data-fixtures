<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\DocumentManager as PhpcrDocumentManager;
use OutOfBoundsException;
use function array_key_exists;
use function array_keys;
use function get_class;
use function method_exists;
use function sprintf;

/**
 * ReferenceRepository class manages references for
 * fixtures in order to easily support the relations
 * between fixtures
 */
class ReferenceRepository
{
    /**
     * List of named references to the fixture objects
     * gathered during loads of fixtures
     *
     * @var array
     */
    private $references = [];

    /**
     * List of identifiers stored for references
     * in case if reference gets unmanaged, it will
     * use a proxy referenced by this identity
     *
     * @var array
     */
    private $identities = [];

    /**
     * Currently used object manager
     *
     * @var ObjectManager
     */
    private $manager;

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
     * @return array
     */
    protected function getIdentifier($reference, $uow)
    {
        // In case Reference is not yet managed in UnitOfWork
        if (! $this->hasIdentifier($reference)) {
            $class = $this->manager->getClassMetadata(get_class($reference));
            
            $values = $class->getIdentifierValues($reference);

            // See https://github.com/doctrine/data-fixtures/issues/135 and
            // https://github.com/doctrine/data-fixtures/issues/167
            foreach ($values as $key => $value) {
                if (!is_scalar($value)) {
                    // To prevent "Array to string conversion" in
                    // UnitOfWork::tryGetById, we can only return a single
                    // identifier. This does mean that related entities that
                    // themselves have a composite primary key are unsupported.
                    $proxyId = $this->getIdentifier($value, $uow);
                    $keys = array_keys($proxyId);
                    $values[$key] = $proxyId[$keys[0]];
                }
            }

            return $values;
        }

        // Dealing with ORM UnitOfWork
        if (method_exists($uow, 'getEntityIdentifier')) {
            return $uow->getEntityIdentifier($reference);
        }

        // PHPCR ODM UnitOfWork
        if ($this->manager instanceof PhpcrDocumentManager) {
            return $uow->getDocumentId($reference);
        }

        // ODM UnitOfWork
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

        if (! $this->hasIdentifier($reference)) {
            return;
        }

        // in case if reference is set after flush, store its identity
        $uow                     = $this->manager->getUnitOfWork();
        $this->identities[$name] = $this->getIdentifier($reference, $uow);
    }

    /**
     * Store the identifier of a reference
     *
     * @param string $name
     * @param mixed  $identity
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
     *
     * @return void
     *
     * @throws BadMethodCallException - if repository already has a reference by $name.
     */
    public function addReference($name, $object)
    {
        if (isset($this->references[$name])) {
            throw new BadMethodCallException(sprintf('Reference to "%s" already exists, use method setReference in order to override it', $name));
        }

        $this->setReference($name, $object);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @param string $name
     *
     * @return object
     *
     * @throws OutOfBoundsException - if repository does not exist.
     */
    public function getReference($name)
    {
        if (! $this->hasReference($name)) {
            throw new OutOfBoundsException(sprintf('Reference to "%s" does not exist', $name));
        }

        $reference = $this->references[$name];
        $meta      = $this->manager->getClassMetadata(get_class($reference));

        if (! $this->manager->contains($reference) && isset($this->identities[$name])) {
            $reference               = $this->manager->getReference(
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
     *
     * @return bool
     */
    public function hasReference($name)
    {
        return isset($this->references[$name]);
    }

    /**
     * Searches for reference names in the
     * list of stored references
     *
     * @param object $reference
     *
     * @return array
     */
    public function getReferenceNames($reference)
    {
        return array_keys($this->references, $reference, true);
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
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Checks if object has identifier already in unit of work.
     *
     * @param string $reference
     *
     * @return bool
     */
    private function hasIdentifier($reference)
    {
        // in case if reference is set after flush, store its identity
        $uow = $this->manager->getUnitOfWork();

        if ($this->manager instanceof PhpcrDocumentManager) {
            return $uow->contains($reference);
        }

        return $uow->isInIdentityMap($reference);
    }
}
