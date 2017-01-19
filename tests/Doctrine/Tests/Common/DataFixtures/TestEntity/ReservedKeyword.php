<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

/**
 * @Entity
 * @Table("SELECT")
 */
class ReservedKeyword
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;
}
