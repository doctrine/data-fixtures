<?php
namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\UserRole;

class UserRoleFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $userRole = new UserRole(
            $this->getReference('admin'),
            $this->getReference('admin-role')
        );
        
        $manager->persist($userRole);
        $this->referenceRepository->addReference('composite-key', $userRole);
        $manager->flush();
    }
}
