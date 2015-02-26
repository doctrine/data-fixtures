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

    /**
     * @ManyToMany(targetEntity="Doctrine\Tests\Common\DataFixtures\TestEntity\User", inversedBy="authors")
     * @JoinTable(name="author_reader", schema="readers",
     *      joinColumns={@JoinColumn(name="author_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="reader_id", referencedColumnName="id")}
     * )
     *
     * @var User[]
     */
    private $readers;

    /**
     * @ManyToMany(targetEntity="Doctrine\Tests\Common\DataFixtures\TestEntity\User", mappedBy="readers")
     *
     * @var User[]
     */
    private $authors;

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

    /**
     * @return User[]
     */
    public function getReaders()
    {
        return $this->readers;
    }

    /**
     * @param User[] $readers
     * @return User
     */
    public function setReaders($readers)
    {
        $this->readers = $readers;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @param User[] $authors
     * @return User
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;

        return $this;
    }
}
