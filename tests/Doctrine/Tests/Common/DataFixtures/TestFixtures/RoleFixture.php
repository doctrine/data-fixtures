<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;

class RoleFixture implements SharedFixtureInterface
{
    /** @var ReferenceRepository|null */
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function load(ObjectManager $manager)
    {
        $adminRole = new Role();
        $adminRole->setName('admin');

        $manager->persist($adminRole);
        $this->referenceRepository->addReference('admin-role', $adminRole);
        $manager->flush();
    }
}
