<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures;

/**
 * Shared Fixture interface needs to be implemented
 * by fixtures, which needs some references to be shared
 * among other fixture classes in order to maintain
 * relation mapping
 */
interface SharedFixtureInterface extends FixtureInterface
{
    /** @return void */
    public function setReferenceRepository(ReferenceRepository $referenceRepository);
}
