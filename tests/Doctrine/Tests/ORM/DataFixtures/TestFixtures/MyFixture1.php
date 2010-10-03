<?php

namespace TestFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\DataFixtures\Fixture;

class MyFixture1 implements Fixture
{
    public function load(EntityManager $em)
    {
    }
}