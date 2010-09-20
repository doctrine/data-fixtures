<?php

namespace TestFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\DataFixtures\Fixture;

class MyFixture1 implements Fixture
{
    public $loaded = false;

    public function load(EntityManager $em)
    {
        $this->loaded = true;
    }
}