<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;

class RoleFixture implements SharedFixtureInterface
{
    private ReferenceRepository|null $referenceRepository = null;

    public function setReferenceRepository(ReferenceRepository $referenceRepository): void
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $adminRole = new Role();
        $adminRole->setName('admin');

        $manager->persist($adminRole);
        $this->referenceRepository->addReference('admin-role', $adminRole);
        $manager->flush();
    }
}
