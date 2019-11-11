<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

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
