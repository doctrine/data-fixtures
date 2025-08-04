<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\ODM\PHPCR\DocumentManager as PhpcrDocumentManager;
use Doctrine\ORM\UnitOfWork as OrmUnitOfWork;
use Doctrine\Persistence\ObjectManager;
use OutOfBoundsException;

use function array_key_exists;
use function array_keys;
use function array_map;
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
     * @phpstan-var array<class-string, array<string|int, object>>
     */
    private array $referencesByClass = [];

    /**
     * List of identifiers stored for references
     * in case a reference gets no longer managed, it will
     * use a proxy referenced by this identity
     *
     * @phpstan-var array<class-string, array<string, mixed>>
     */
    private array $identitiesByClass = [];

    /**
     * Currently used object manager
     */
    private ObjectManager $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get identifier for a unit of work
     *
     * @param object $reference Reference object
     * @param object $uow       Unit of work
     */
    protected function getIdentifier(object $reference, object $uow): mixed
    {
        // In case Reference is not yet managed in UnitOfWork
        if (! $this->hasIdentifier($reference)) {
            $class = $this->manager->getClassMetadata($reference::class);

            return $class->getIdentifierValues($reference);
        }

        // Dealing with ORM UnitOfWork
        if ($uow instanceof OrmUnitOfWork) {
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
     */
    public function setReference(string $name, object $reference): void
    {
        $class = $this->getRealClass($reference::class);

        $this->referencesByClass[$class][$name] = $reference;

        if (! $this->hasIdentifier($reference)) {
            return;
        }

        // in case if reference is set after flush, store its identity
        $uow        = $this->manager->getUnitOfWork();
        $identifier = $this->getIdentifier($reference, $uow);

        $this->identitiesByClass[$class][$name] = $identifier;
    }

    /**
     * Store the identifier of a reference
     *
     * @param class-string $class
     */
    public function setReferenceIdentity(string $name, mixed $identity, string $class): void
    {
        $this->identitiesByClass[$class][$name] = $identity;
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
     * @param object $object - managed object
     *
     * @throws BadMethodCallException - if repository already has a reference by $name.
     */
    public function addReference(string $name, object $object): void
    {
        $class = $this->getRealClass($object::class);
        if (isset($this->referencesByClass[$class][$name])) {
            throw new BadMethodCallException(sprintf(
                'Reference to "%s" for class "%s" already exists, use method setReference() in order to override it',
                $name,
                $class,
            ));
        }

        $this->setReference($name, $object);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @phpstan-param class-string<T> $class
     *
     * @phpstan-return T
     *
     * @throws OutOfBoundsException - if repository does not exist.
     *
     * @template T of object
     */
    public function getReference(string $name, string $class): object
    {
        if (! $this->hasReference($name, $class)) {
            throw new OutOfBoundsException(sprintf('Reference to "%s" for class "%s" does not exist', $name, $class));
        }

        $reference = $this->referencesByClass[$class][$name];

        $identity = ($this->identitiesByClass[$class][$name] ?? null);

        $meta = $this->manager->getClassMetadata($class);

        if (! $this->manager->contains($reference) && $identity !== null) {
            $reference                              = $this->manager->getReference($meta->name, $identity);
            $this->referencesByClass[$class][$name] = $reference; // already in identity map
        }

        return $reference;
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @phpstan-param class-string $class
     */
    public function hasReference(string $name, string $class): bool
    {
        return isset($this->referencesByClass[$class][$name]);
    }

    /**
     * Searches for reference names in the
     * list of stored references
     *
     * @return array<string>
     */
    public function getReferenceNames(object $reference): array
    {
        $class = $this->getRealClass($reference::class);
        if (! isset($this->referencesByClass[$class])) {
            return [];
        }

        return array_map('strval', array_keys($this->referencesByClass[$class], $reference, true));
    }

    /**
     * Checks if reference has identity stored
     *
     * @param class-string $class
     */
    public function hasIdentity(string $name, string $class): bool
    {
        return array_key_exists($class, $this->identitiesByClass) && array_key_exists($name, $this->identitiesByClass[$class]);
    }

    /**
     * Get all stored identities
     *
     * @phpstan-return array<class-string, array<string, mixed>>
     */
    public function getIdentitiesByClass(): array
    {
        return $this->identitiesByClass;
    }

    /**
     * Get all stored references
     *
     * @phpstan-return array<class-string, array<string|int, object>>
     */
    public function getReferencesByClass(): array
    {
        return $this->referencesByClass;
    }

    /**
     * Get object manager
     */
    public function getManager(): ObjectManager
    {
        return $this->manager;
    }

    /**
     * Get real class name of a reference that could be a proxy
     *
     * @param string $className Class name of reference object
     *
     * @return class-string
     */
    protected function getRealClass(string $className): string
    {
        return $this->manager->getClassMetadata($className)->getName();
    }

    /**
     * Checks if object has identifier already in unit of work.
     */
    private function hasIdentifier(object $reference): bool
    {
        // in case if reference is set after flush, store its identity
        $uow = $this->manager->getUnitOfWork();

        if ($this->manager instanceof PhpcrDocumentManager) {
            return $uow->contains($reference);
        }

        return $uow->isInIdentityMap($reference);
    }
}
