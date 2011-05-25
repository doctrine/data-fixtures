<?php

namespace TestFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;

class MyFixture1 implements FixtureInterface
{
    public function load(EntityManager $manager)
    {
    }
}