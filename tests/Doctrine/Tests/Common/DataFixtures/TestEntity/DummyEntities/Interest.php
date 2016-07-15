<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities;

/**
 * @Entity
 */
class Interest extends ParentInterest
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="Account", inversedBy="interests")
     * @JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;

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
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }
}