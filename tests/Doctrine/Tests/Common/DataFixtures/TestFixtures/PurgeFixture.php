<?php

namespace Doctrine\Tests\Common\DataFixtures\TestFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Account;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Game;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Interest;
use Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities\Winner;

class PurgeFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $interest = new Interest();

        $account = new Account();
        $account->addInterest($interest);

        $winner = new Winner();
        $winner->addAccount($account);

        $game = new Game();
        $game->setWinner($winner);

        $manager->persist($winner);
        $manager->persist($account);
        $manager->persist($interest);
        $manager->persist($game);
        $manager->flush();
    }
}