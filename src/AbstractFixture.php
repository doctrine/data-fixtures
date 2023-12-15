<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;

use function assert;

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
     * @var ReferenceRepository|null
     */
    protected $referenceRepository;

    /**
     * {@inheritDoc}
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    private function getReferenceRepository(): ReferenceRepository
    {
        assert($this->referenceRepository !== null);

        return $this->referenceRepository;
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * already is set, it overrides it
     *
     * @see ReferenceRepository::setReference()
     *
     * @param object $object - managed object
     */
    public function setReference(string $name, object $object): void
    {
        $this->getReferenceRepository()->setReference($name, $object);
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * already is set, it throws a
     * BadMethodCallException exception
     *
     * @see ReferenceRepository::addReference()
     *
     * @param object $object - managed object
     *
     * @throws BadMethodCallException - if repository already has a reference by $name.
     */
    public function addReference(string $name, object $object): void
    {
        $this->getReferenceRepository()->addReference($name, $object);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @see ReferenceRepository::getReference()
     *
     * @psalm-param class-string<T> $class
     *
     * @psalm-return T
     *
     * @template T of object
     */
    public function getReference(string $name, string $class): object
    {
        return $this->getReferenceRepository()->getReference($name, $class);
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @see ReferenceRepository::hasReference()
     *
     * @psalm-param class-string $class
     */
    public function hasReference(string $name, string $class): bool
    {
        return $this->getReferenceRepository()->hasReference($name, $class);
    }
}
