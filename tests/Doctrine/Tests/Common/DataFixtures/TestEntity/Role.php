<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

/**
 * @Entity
 */
class Role
{
    /**
     * @Column(type="role_id")
     * @Id
     */
    private $id;

    /**
     * @Column(length=50)
     */
    private $name;

    /**
     * Role constructor.
     * @param $id
     */
    public function __construct($id = null)
    {
        $this->id = $id ?? new RoleId('uuid');
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
