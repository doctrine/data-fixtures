<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MyFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
    }
}
