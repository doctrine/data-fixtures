<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity\DummyEntities;

/**
 * @Entity
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"parent" = "ParentInterest", "child" = "Interest"})
 */
class ParentInterest 
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="ParentInterest")
     * @JoinColumn(name="self_id", referencedColumnName="id")
     */
    private $parent;
}