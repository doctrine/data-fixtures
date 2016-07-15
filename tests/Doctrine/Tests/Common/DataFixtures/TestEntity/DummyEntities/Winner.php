<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities;

/**
 * @Entity
 */
class Winner
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @OneToMany(targetEntity="Account", mappedBy="winner", cascade={"persist"})
     */
    private $accounts;
    
    /**
     * @OneToOne(targetEntity="Game", mappedBy="game", cascade={"persist"})
     */
    private $game;

    /**
     * @OneToOne(targetEntity="ParentInterest")
     * @JoinColumn(name="interest_id", referencedColumnName="id")
     */
    private $interest;

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
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @param mixed $account
     */
    public function addAccount(Account $account)
    {
        $this->accounts[] = $account;

        $account->setWinner($this);
    }

    /**
     * @return mixed
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param mixed $game
     */
    public function setGame(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return mixed
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * @param mixed $interest
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
    }
}