<?php

namespace TestFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MyFixture1 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
    }
}
