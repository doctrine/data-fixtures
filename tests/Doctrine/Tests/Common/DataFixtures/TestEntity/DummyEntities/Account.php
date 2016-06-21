<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities;

/**
 * @Entity
 */
class Account
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Winner", inversedBy="accounts")
     * @JoinColumn(name="winner_id", referencedColumnName="id")
     */
    private $winner;

    /**
     * @OneToMany(targetEntity="Interest", mappedBy="account", cascade={"persist"})
     */
    private $interests;

    /**
     * @OneToMany(targetEntity="Game", mappedBy="account", cascade={"persist"})
     */
    private $games;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param mixed $winner
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;
    }

    /**
     * @return mixed
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * @param Interest $interest
     */
    public function addInterest(Interest $interest)
    {
        $this->interests[] = $interest;
        
        $interest->setAccount($this);
    }

    /**
     * @param Game $game
     */
    public function addGame(Game $game)
    {
        $this->games[] = $game;

        $game->setAccount($this);
    }
}