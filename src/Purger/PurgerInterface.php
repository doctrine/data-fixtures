<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

/**
 * PurgerInterface
 */
interface PurgerInterface
{
    /**
     * Purge the data from the database for the given EntityManager.
     */
    public function purge(): void;
}
