<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\User;

class UniqueRoleUserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setId(5);
        $admin->setCode('008');
        $admin->setEmail('admin-unique-role@example.com');
        $admin->setPassword('secret');
        $role = $this->getUniqueReference('role');
        $admin->setRole($role);

        $manager->persist($admin);
        $manager->flush();

        $this->addUniqueReference('admin-unique', $admin, 'user');
    }
}
