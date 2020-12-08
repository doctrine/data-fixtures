<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use function md5;

/**
 * @ORM\Entity
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(length=32)
     * @ORM\Id
     *
     * @var string|null
     */
    private $code;

    /**
     * @ORM\Column(length=32)
     *
     * @var string|null
     */
    private $password;

    /**
     * @ORM\Column(length=255)
     *
     * @var string|null
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, cascade={"persist"})
     *
     * @var Role|null
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="authors")
     * @ORM\JoinTable(name="author_reader", schema="readers",
     *      joinColumns={@ORM\JoinColumn(name="author_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reader_id", referencedColumnName="id")}
     * )
     *
     * @var User[]|Collection
     */
    private $readers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="readers")
     *
     * @var User[]|Collection
     */
    private $authors;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
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
     * @return User[]|Collection
     */
    public function getReaders()
    {
        return $this->readers;
    }

    /**
     * @param User[]|Collection $readers
     *
     * @return User
     */
    public function setReaders($readers)
    {
        $this->readers = $readers;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @param User[]|Collection $authors
     *
     * @return User
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;

        return $this;
    }
}
