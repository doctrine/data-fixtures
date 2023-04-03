<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Deprecations\Deprecation;
use Doctrine\ODM\PHPCR\DocumentManager as PhpcrDocumentManager;
use Doctrine\Persistence\ObjectManager;
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
     * gathered during fixure loading
     *
     * @psalm-var array<string, object>
     */
    private $references = [];

    /**
     * List of named references to the fixture objects
     * gathered during fixure loading
     *
     * @psalm-var array<class-string, array<string, object>>
     */
    private $referencesByClass = [];

    /**
     * List of identifiers stored for references
     * in case a reference gets no longer managed, it will
     * use a proxy referenced by this identity
     *
     * @psalm-var array<string, mixed>
     */
    private $identities = [];

    /**
     * List of identifiers stored for references
     * in case a reference gets no longer managed, it will
     * use a proxy referenced by this identity
     *
     * @psalm-var array<class-string, array<string, mixed>>
     */
    private $identitiesByClass = [];

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

            return $class->getIdentifierValues($reference);
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
     *
     * @return void
     */
    public function setReference($name, $reference)
    {
        $class = $this->getRealClass(get_class($reference));

        $this->referencesByClass[$class][$name] = $reference;

        // For BC, to be removed in next major.
        $this->references[$name] = $reference;

        if (! $this->hasIdentifier($reference)) {
            return;
        }

        // in case if reference is set after flush, store its identity
        $uow        = $this->manager->getUnitOfWork();
        $identifier = $this->getIdentifier($reference, $uow);

        $this->identitiesByClass[$class][$name] = $identifier;

        // For BC, to be removed in next major.
        $this->identities[$name] = $identifier;
    }

    /**
     * Store the identifier of a reference
     *
     * @param string            $name
     * @param mixed             $identity
     * @param class-string|null $class
     *
     * @return void
     */
    public function setReferenceIdentity($name, $identity, ?string $class = null)
    {
        if ($class === null) {
            Deprecation::trigger(
                'doctrine/data-fixtures',
                'https://github.com/doctrine/data-fixtures/pull/409',
                'Argument $class of %s() will be mandatory in 2.0.',
                __METHOD__
            );
        }

        $this->identitiesByClass[$class][$name] = $identity;

        // For BC, to be removed in next major.
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
        // For BC, to be removed in next major.
        if (isset($this->references[$name])) {
            throw new BadMethodCallException(sprintf(
                'Reference to "%s" already exists, use method setReference() in order to override it',
                $name
            ));
        }

        $class = $this->getRealClass(get_class($object));
        if (isset($this->referencesByClass[$class][$name])) {
            throw new BadMethodCallException(sprintf(
                'Reference to "%s" for class "%s" already exists, use method setReference() in order to override it',
                $name,
                $class
            ));
        }

        $this->setReference($name, $object);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @param string $name
     * @psalm-param class-string<T>|null $class
     *
     * @return object
     * @psalm-return ($class is null ? object : T)
     *
     * @throws OutOfBoundsException - if repository does not exist.
     *
     * @template T of object
     */
    public function getReference($name, ?string $class = null)
    {
        if ($class === null) {
            Deprecation::trigger(
                'doctrine/data-fixtures',
                'https://github.com/doctrine/data-fixtures/pull/409',
                'Argument $class of %s() will be mandatory in 2.0.',
                __METHOD__
            );
        }

        if (! $this->hasReference($name, $class)) {
            // For BC, to be removed in next major.
            if ($class === null) {
                throw new OutOfBoundsException(sprintf('Reference to "%s" does not exist', $name));
            }

            throw new OutOfBoundsException(sprintf('Reference to "%s" for class "%s" does not exist', $name, $class));
        }

        $reference = $class === null
            ? $this->references[$name] // For BC, to be removed in next major.
            : $this->referencesByClass[$class][$name];

        $identity = $class === null
            ? ($this->identities[$name] ?? null) // For BC, to be removed in next major.
            : ($this->identitiesByClass[$class][$name] ?? null);

        if ($class === null) { // For BC, to be removed in next major.
            $class = $this->getRealClass(get_class($reference));
        }

        $meta = $this->manager->getClassMetadata($class);

        if (! $this->manager->contains($reference) && $identity !== null) {
            $reference                              = $this->manager->getReference($meta->name, $identity);
            $this->references[$name]                = $reference; // already in identity map
            $this->referencesByClass[$class][$name] = $reference; // already in identity map
        }

        return $reference;
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @param string $name
     * @psalm-param class-string $class
     *
     * @return bool
     */
    public function hasReference($name, ?string $class = null)
    {
        if ($class === null) {
            Deprecation::trigger(
                'doctrine/data-fixtures',
                'https://github.com/doctrine/data-fixtures/pull/409',
                'Argument $class of %s() will be mandatory in 2.0.',
                __METHOD__
            );
        }

        return $class === null
            ? isset($this->references[$name]) // For BC, to be removed in next major.
            : isset($this->referencesByClass[$class][$name]);
    }

    /**
     * Searches for reference names in the
     * list of stored references
     *
     * @param object $reference
     *
     * @return array<string>
     */
    public function getReferenceNames($reference)
    {
        $class = $this->getRealClass(get_class($reference));
        if (! isset($this->referencesByClass[$class])) {
            return [];
        }

        return array_keys($this->referencesByClass[$class], $reference, true);
    }

    /**
     * Checks if reference has identity stored
     *
     * @param string            $name
     * @param class-string|null $class
     *
     * @return bool
     */
    public function hasIdentity($name, ?string $class = null)
    {
        if ($class === null) {
            Deprecation::trigger(
                'doctrine/data-fixtures',
                'https://github.com/doctrine/data-fixtures/pull/409',
                'Argument $class of %s() will be mandatory in 2.0.',
                __METHOD__
            );
        }

        return $class === null
            ? array_key_exists($name, $this->identities) // For BC, to be removed in next major.
            : array_key_exists($class, $this->identitiesByClass) && array_key_exists($name, $this->identitiesByClass[$class]);
    }

    /**
     * @deprecated in favor of getIdentitiesByClass
     *
     * Get all stored identities
     *
     * @psalm-return array<string, object>
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * Get all stored identities
     *
     * @psalm-return array<class-string, array<string, object>>
     */
    public function getIdentitiesByClass(): array
    {
        return $this->identitiesByClass;
    }

    /**
     * @deprecated in favor of getReferencesByClass
     *
     * Get all stored references
     *
     * @psalm-return array<string, object>
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Get all stored references
     *
     * @psalm-return array<class-string, array<string, object>>
     */
    public function getReferencesByClass(): array
    {
        return $this->referencesByClass;
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
     * Get real class name of a reference that could be a proxy
     *
     * @param string $className Class name of reference object
     *
     * @return string
     */
    protected function getRealClass($className)
    {
        return $this->manager->getClassMetadata($className)->getName();
    }

    /**
     * Checks if object has identifier already in unit of work.
     *
     * @param object $reference
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
