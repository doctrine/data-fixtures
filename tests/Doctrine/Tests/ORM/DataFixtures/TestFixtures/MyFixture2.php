<?php

namespace TestFixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\DataFixtures\Fixture;

class MyFixture2 implements Fixture
{
    public $loaded = false;

    public function load(EntityManager $em)
    {
        $this->loaded = true;
    }
}