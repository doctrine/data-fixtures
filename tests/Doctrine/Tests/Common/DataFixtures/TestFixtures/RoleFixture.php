<?php

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;

class RoleFixture implements SharedFixtureInterface
{
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function load($manager)
    {
        $adminRole = new Role();
        $adminRole->setName('admin');

        $manager->persist($adminRole);
        $this->referenceRepository->addReference('admin-role', $adminRole);
        $manager->flush();
    }
}