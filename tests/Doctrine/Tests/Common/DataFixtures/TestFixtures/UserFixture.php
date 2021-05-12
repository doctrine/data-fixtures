<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;

class UserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setId(5);
        $admin->setCode('008');
        $admin->setEmail('admin@example.com');
        $admin->setPassword('secret');
        $role = $this->getReference('admin-role');
        $admin->setRole($role);
        $this->addReference('admin', $admin);

        $manager->persist($admin);

        $uniqueAdmin = new User();
        $uniqueAdmin->setId(6);
        $uniqueAdmin->setCode('009');
        $uniqueAdmin->setEmail('admin-unique@example.com');
        $uniqueAdmin->setPassword('secret');
        $role = $this->getUniqueReference('admin-role-unique', 'role');
        $uniqueAdmin->setRole($role);
        $this->addUniqueReference('admin-unique', $uniqueAdmin, 'user');

        $manager->persist($uniqueAdmin);

        $uniqueAdmin2 = new User();
        $uniqueAdmin2->setId(7);
        $uniqueAdmin2->setCode('010');
        $uniqueAdmin2->setEmail('admin-unique-2@example.com');
        $uniqueAdmin2->setPassword('secret');
        $role = $this->getUniqueReference('admin-role-unique-2', 'role');
        $uniqueAdmin2->setRole($role);
        $this->addUniqueReference('admin-unique-2', $uniqueAdmin2, 'user');

        $manager->persist($uniqueAdmin2);

        $manager->flush();
    }
}
