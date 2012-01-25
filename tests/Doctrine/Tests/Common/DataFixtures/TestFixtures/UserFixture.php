<?php
namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new User;
        $admin->setId(4);
        $admin->setCode('007');
        $admin->setEmail('admin@example.com');
        $admin->setPassword('secret');
        $role = $this->getReference('admin-role');
        $admin->setRole($role);

        $manager->persist($admin);
        $manager->flush();

        $this->addReference('admin', $admin);
    }
}
