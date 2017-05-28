<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

/**
 * @Entity
 * @Table("`SELECT`")
 */
class Quoted
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(length=50, name="select")
     */
    private $select;

    /**
     * @ManyToMany(targetEntity="Quoted")
     * @JoinTable(name="`INSERT`",
     *      joinColumns={@JoinColumn(name="`SELECT`", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="`UPDATE`", referencedColumnName="id")}
     * )
     */
    private $selects;
}
