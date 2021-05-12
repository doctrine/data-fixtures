<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;

class RoleFixture implements SharedFixtureInterface
{
    /** @var ReferenceRepository|null */
    private $referenceRepository;

    public function setReferenceRepository(ReferenceRepository $referenceRepository): void
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $adminRole = $this->persistNewRole('admin', $manager);
        $this->referenceRepository->addReference('admin-role', $adminRole);

        $taggedAdminRole = $this->persistNewRole('admin-role-tagged', $manager);
        $this->referenceRepository->addReference('admin-role-tagged', $taggedAdminRole, 'tag');

        $uniqueAdminRole = $this->persistNewRole('admin-role-unique', $manager);
        $this->referenceRepository->addUniqueReference('admin-role-unique', $uniqueAdminRole, 'role');

        $uniqueAdminRole = $this->persistNewRole('admin-role-unique-2', $manager);
        $this->referenceRepository->addUniqueReference('admin-role-unique-2', $uniqueAdminRole, 'role');

        $manager->flush();
    }

    private function persistNewRole($name, $manager)
    {
        $role = new Role();
        $role->setName($name);

        $manager->persist($role);

        return $role;
    }
}
