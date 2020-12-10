<?php

declare(strict_types=1);

namespace TestFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyFixture2 implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}
