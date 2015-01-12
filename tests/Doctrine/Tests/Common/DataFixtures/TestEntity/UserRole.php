<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

/**
 * @Entity
 */
class UserRole
{
    /**
     * @ManyToOne(targetEntity="User")
     * @Id
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Role")
     * @Id
     */
    private $role;
    
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getRole()
    {
        return $this->role;
    }
}
