<?php

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

/**
 * @Entity
 */
class User
{
    /**
     * @Column(type="integer")
     * @Id
     */
    private $id;
    
    /**
     * @Column(length=32)
     * @Id
     */
    private $code;

    /**
     * @Column(length=32)
     */
    private $password;

    /**
     * @Column(length=255)
     */
    private $email;

    /**
     * @ManyToOne(targetEntity="Role", cascade={"persist"})
     */
    private $role;

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setCode($code)
    {
        $this->code = $code;
    }
    
    public function setPassword($password)
    {
        $this->password = md5($password);
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}