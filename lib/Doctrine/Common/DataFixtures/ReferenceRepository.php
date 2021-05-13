<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Common\DataFixtures\Exception\UniqueReferencesStockExhaustedException;
use Doctrine\ODM\PHPCR\DocumentManager as PhpcrDocumentManager;
use Doctrine\Persistence\ObjectManager;
use OutOfBoundsException;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_rand;
use function get_class;
use function in_array;
use function method_exists;
use function sprintf;

/**
 * ReferenceRepository class manages references for
 * fixtures in order to easily support the relations
 * between fixtures.
 */
class ReferenceRepository
{
    /**
     * List of named references to the fixture objects
     * gathered during loads of fixtures.
     *
     * @var array
     */
    private $references = [];

    /**
     * List of tags that contains unique references to
     * the fixture objects gathered during loads of
     * fixtures.
     *
     * @var array
     */
    private $uniqueReferencesTag = [];

    /**
     * List of obsolete unique references.
     *
     * @var array
     */
    private $obsoleteReferences = [];

    /**
     * List of tagged references to the
     * fixture objects gathered during loads
     * of fixtures.
     *
     * @var array
     */
    private $taggedReferences = [];

    /**
     * List of identifiers stored for references
     * in case if reference gets unmanaged, it will
     * use a proxy referenced by this identity.
     *
     * @var array
     */
    private $identities = [];

    /**
     * Currently used object manager.
     *
     * @var ObjectManager
     */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get identifier for a unit of work.
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
     * and referenced to $reference.
     * If $tag is not null, create a tagged reference
     * If $name is already set, it overrides it.
     *
     * @param string $name
     * @param object $reference
     * @param null|string $tag
     */
    public function setReference($name, $reference, $tag = null)
    {
        $this->doSetReference($name, $reference, $tag);
    }

    /**
     * Set the reference entry tagged with $tag,
     * identified by $name and referenced to
     * $reference.
     * If $name is already set, it overrides it.
     *
     * @param string $name
     * @param object $reference
     * @param string $tag
     */
    public function setUniqueReference($name, $reference, $tag)
    {
        if (!in_array($tag, $this->uniqueReferencesTag)) {
            $this->uniqueReferencesTag[] = $tag;
        }

        $this->doSetReference($name, $reference, $tag);
    }

    /**
     * Store the identifier of a reference.
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
     * not be set yet.
     *
     * Notice: in case if identifier is generated after
     * the record is inserted, be sure tu use this method
     * after $object is flushed
     *
     * @param string $name
     * @param object $object - managed object
     * @param null|string $name
     *
     * @return void
     *
     * @throws BadMethodCallException - if repository already has a reference by $name.
     */
    public function addReference($name, $object, $tag = null)
    {
        if (isset($this->references[$name])) {
            throw new BadMethodCallException(sprintf('Reference to "%s" already exists, use method setReference in order to override it', $name));
        }

        if (isset($this->taggedReferences[$tag][$name])) {
            throw new BadMethodCallException(sprintf(
                'Reference "%s" tagged as "%s" already exists, use method setReference in order to override it.',
                $name,
                $tag
            ));
        }

        $this->setReference($name, $object, $tag);
    }

    /**
     * Set the unique reference entry tagged with
     * $tag, identified by $name and referenced
     * to managed $object.
     * $name must not be set yet.
     *
     * Notice: in case if identifier is generated after
     * the record is inserted, be sure tu use this method
     * after $object is flushed
     *
     * @param string $name
     * @param object $object - managed object
     * @param string $tag
     *
     * @return void
     *
     * @throws BadMethodCallException - if repository already has a unique reference by $name.
     */
    public function addUniqueReference($name, $object, $tag)
    {
        if ($tag && isset($this->taggedReferences[$tag][$name])) {
            throw new BadMethodCallException(sprintf(
                'Unique reference "%s" tagged as "%s" already exists, use method setUniqueReference in order to override it.',
                $name,
                $tag
            ));
        }

        $this->setUniqueReference($name, $object, $tag);
    }

    /**
     * Loads an object using stored reference
     * named by $name.
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

        return $this->consultIdentityMap($name, $this->references[$name]);
    }

    /**
     * Loads an object using stored unique reference
     * named by $name.
     *
     * @param string $name
     * @param string $tag
     *
     * @return object
     *
     * @throws OutOfBoundsException|UniqueReferencesStockExhaustedException
     */
    public function getUniqueReference($name, $tag)
    {
        if (!$this->hasTaggedReferences($tag)) {
            throw new OutOfBoundsException(sprintf('There are no unique reference tagged as "%s".', $tag));
        }

        if (isset($this->obsoleteReferences[$tag]) && in_array($name, $this->obsoleteReferences[$tag])) {
            throw new OutOfBoundsException(sprintf('Unique reference to "%s" has already been used.', $name));
        }

        if (empty($this->taggedReferences[$tag])) {
            throw new UniqueReferencesStockExhaustedException(sprintf(
                'The stock of unique references tagged as "%s" is exhausted, create more or use less.',
                $tag
            ));
        }

        if (!isset($this->taggedReferences[$tag][$name])) {
            throw new OutOfBoundsException(sprintf('Unique reference to "%s" tagged with "%s" does not exist.', $name, $tag));
        }

        $reference = $this->consultIdentityMap($name, $this->taggedReferences[$tag][$name]);
        unset($this->taggedReferences[$tag][$name]);
        // Keeps record of obsolete references to indicate more precise errors to users.
        $this->obsoleteReferences[$tag][] = $name;

        return $reference;
    }

    /**
     * Get a reference tagged with $tag.
     *
     * @param string $tag
     *
     * @return object
     *
     * @throws OutOfBoundsException|UniqueReferencesStockExhaustedException
     */
    public function getRandomReference($tag)
    {
        if (! $this->hasTaggedReferences($tag)) {
            throw new OutOfBoundsException(sprintf('There are no unique reference tagged as "%s".', $tag));
        }

        // Only possible with unique references
        if (empty($this->taggedReferences[$tag])) {
            throw new UniqueReferencesStockExhaustedException(sprintf(
                'The stock of unique references tagged as "%s" is exhausted, create more or use less.',
                $tag
            ));
        }

        $name = array_rand($this->taggedReferences[$tag]);
        $reference = $this->taggedReferences[$tag][$name];

        if (in_array($tag, $this->uniqueReferencesTag)) {
            unset($this->taggedReferences[$tag][$name]);
        }

        return $this->consultIdentityMap($name, $reference, $tag);
    }

    /**
     * Check if an object is stored using reference
     * named by $name.
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
     * Check if an object is stored using unique
     * reference named by $name and tagged by $tag.
     *
     * @param string $name
     * @param string $tag
     *
     * @return bool
     */
    public function hasTaggedReference($name, $tag)
    {
        return isset($this->taggedReferences[$tag][$name]);
    }

    /**
     * Checks if there are unique references tagged
     * with $tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function hasTaggedReferences($tag)
    {
        return isset($this->taggedReferences[$tag]);
    }

    /**
     * Searches for reference names in the
     * list of stored references.
     *
     * @param object $reference
     *
     * @return array
     */
    public function getReferenceNames($reference)
    {
        foreach ($this->taggedReferences as $taggedReferences) {
            $this->references = array_merge($this->references, $taggedReferences);
        }

        return array_keys($this->references, $reference, true);
    }

    /**
     * Checks if reference has identity stored.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasIdentity($name)
    {
        return array_key_exists($name, $this->identities);
    }

    /**
     * Get all stored identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }

    /**
     * Get all stored references.
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Get all stored unique references.
     *
     * @return array
     */
    public function getUniqueReferences()
    {
        $allUniqueReferences = [];
        foreach ($this->uniqueReferencesTag as $tag) {
            $allUniqueReferences = array_merge($allUniqueReferences, $this->taggedReferences[$tag]);
        }

        return $allUniqueReferences;
    }

    /**
     * Get all references stored
     * tagged with $tag.
     * Use allUniqueReferences method for
     * retrieves all unique references.
     *
     * @return array
     *
     * @var string $tag
     */
    public function getReferencesByTag($tag)
    {
        if (! $this->hasTaggedReferences($tag)) {
            throw new OutOfBoundsException(sprintf('There are no references for "%s".', $tag));
        }

        return $this->taggedReferences[$tag];
    }

    /**
     * Get object manager.
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

    /**
     * Set the reference entry identified by $name
     * and referenced to $reference.
     * if $tag is not null create a unique reference.
     * If $name is already set, it overrides it.
     *
     * @param string $name
     * @param object $reference
     * @param null|string tag
     *
     * @return void
     */
    private function doSetReference($name, $reference, $tag = null)
    {
        if ($tag) {
            $this->taggedReferences[$tag][$name] = $reference;
        } else {
            $this->references[$name] = $reference;
        }

        if (! $this->hasIdentifier($reference)) {
            return;
        }

        $uow                     = $this->manager->getUnitOfWork();
        $this->identities[$name] = $this->getIdentifier($reference, $uow);
    }

    /**
     * If the manager does not contain $reference and
     * identities property contains $name, consult the
     * identity map to get the reference.
     *
     * @see Doctrine\ORM\EntityManager::getReference
     *
     * @param string $name
     * @param object $reference
     * @param null $tag
     *
     * @return object
     */
    private function consultIdentityMap($name, $reference, $tag = null)
    {
        $meta = $this->manager->getClassMetadata(get_class($reference));

        if (! $this->manager->contains($reference) && isset($this->identities[$name])) {
            $reference = $this->manager->getReference(
                $meta->name,
                $this->identities[$name]
            );

            if ($tag) {
                $this->taggedReferences[$tag][$name] = $reference; // already in identity map
            } else {
                $this->references[$name] = $reference; // already in identity map
            }
        }

        return $reference;
    }
}
