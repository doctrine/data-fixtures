<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities;

/**
 * @Entity
 */
class Game
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @OneToOne(targetEntity="Winner", inversedBy="game")
     * @JoinColumn(name="winner_id", referencedColumnName="id")
     */
    private $winner;

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
    public function setWinner(Winner $winner)
    {
        $this->winner = $winner;
        
        $winner->setGame($this);
    }
}