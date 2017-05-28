<?php

namespace TestFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MyFixture2 implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
    }
}
