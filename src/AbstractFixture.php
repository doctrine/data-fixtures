<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use BadMethodCallException;
use Doctrine\Deprecations\Deprecation;

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
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::setReference
     *
     * @param string $name
     * @param object $object - managed object
     *
     * @return void
     */
    public function setReference($name, $object)
    {
        $this->getReferenceRepository()->setReference($name, $object);
    }

    /**
     * Set the reference entry identified by $name
     * and referenced to managed $object. If $name
     * already is set, it throws a
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
        $this->getReferenceRepository()->addReference($name, $object);
    }

    /**
     * Loads an object using stored reference
     * named by $name
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::getReference
     *
     * @param string $name
     * @psalm-param class-string<T>|null $class
     *
     * @return object
     * @psalm-return ($class is null ? object : T)
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
                __METHOD__,
            );
        }

        return $this->getReferenceRepository()->getReference($name, $class);
    }

    /**
     * Check if an object is stored using reference
     * named by $name
     *
     * @see Doctrine\Common\DataFixtures\ReferenceRepository::hasReference
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
                __METHOD__,
            );
        }

        return $this->getReferenceRepository()->hasReference($name, $class);
    }
}
