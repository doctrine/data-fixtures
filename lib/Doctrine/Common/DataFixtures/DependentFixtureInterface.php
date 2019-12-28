<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

/**
 * DependentFixtureInterface needs to be implemented by fixtures which depend on other fixtures
 */
interface DependentFixtureInterface
{
    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return class-string[]
     */
    public function getDependencies();
}
