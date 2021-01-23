<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use Doctrine\Persistence\ObjectManager;

use function interface_exists;

/**
 * Interface contract for fixture classes to implement.
 */
interface FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $manager);
}

interface_exists(ObjectManager::class);
