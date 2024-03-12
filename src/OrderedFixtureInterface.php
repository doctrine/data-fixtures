<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

/**
 * Ordered Fixture interface needs to be implemented
 * by fixtures, which needs to have a specific order
 * when being loaded by directory scan for example
 */
interface OrderedFixtureInterface
{
    /**
     * Get the order of this fixture
     */
    public function getOrder(): int;
}
