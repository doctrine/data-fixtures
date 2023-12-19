<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\Role;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;

class UserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setId(4);
        $admin->setCode('007');
        $admin->setEmail('admin@example.com');
        $admin->setPassword('secret');
        $role = $this->getReference('admin-role', Role::class);
        $admin->setRole($role);

        $manager->persist($admin);
        $manager->flush();

        $this->addReference('admin', $admin);
    }
}
