<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;

/**
 * Abstract Fixture class helps to manage references
 * between fixture classes in order to set relations
 * among other fixtures
 */
abstract class AbstractFixture implements SharedFixtureInterface
{
    /**
     * Fixture reference repository
     *
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * {@inheritdoc}
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * already is set, it overrides it
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::setReference
     *
     * @param string $name
     * @param object $object - managed object
     *
     * @return void
     */
    public function setReference($name, $object)
    {
        $this->referenceRepository->setReference($name, $object);
    }

    /**
     * Set the unique reference entry tagged
     * with $tag, identified by $name and
     * referenced to managed $object. If $name
     * already is set, it overrides it.
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::setUniqueReference
     *
     * @param string $name
     * @param object $object - managed object
     * @param string $tag
     *
     * @return void
     */
    public function setUniqueReference($name, $object, $tag)
    {
        $this->referenceRepository->setUniqueReference($name, $object, $tag);
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * is already set, it throws a
     * BadMethodCallException exception
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::addReference
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
        $this->referenceRepository->addReference($name, $object);
    }

    /**
     * Set the unique reference entry tagged with
     * $tag, identified by $name and referenced to
     * managed $object.
     * If $name is already set for the scope of $tag,
     * it throws a BadMethodCallException exception
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::addReference
     *
     * @param string $name
     * @param object $object - managed object
     * @param string $tag    - tag a group of references
     *
     * @return void
     *
     * @throws BadMethodCallException - if repository already has a reference by $name.
     */
    public function addUniqueReference($name, $object, $tag)
    {
        $this->referenceRepository->addUniqueReference($name, $object, $tag);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::getReference
     *
     * @param string $name
     *
     * @return object
     */
    public function getReference($name)
    {
        return $this->referenceRepository->getReference($name);
    }

    /**
     * Load an unique reference tagged by $tag
     *
     * @param string $tag
     *
     * @return object
     */
    public function getUniqueReference($tag)
    {
        return $this->referenceRepository->getUniqueReference($tag);
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::hasReference
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasReference($name)
    {
        return $this->referenceRepository->hasReference($name);
    }
}
