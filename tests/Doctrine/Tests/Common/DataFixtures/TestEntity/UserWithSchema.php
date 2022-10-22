<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use function md5;

/**
 * @ORM\Entity
 * @ORM\Table(name="user",schema="test_schema")
 */
class UserWithSchema
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
     * @ORM\ManyToMany(targetEntity=UserWithSchema::class, inversedBy="authors")
     * @ORM\JoinTable(name="author_reader", schema="readers",
     *      joinColumns={@ORM\JoinColumn(name="author_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reader_id", referencedColumnName="id")}
     * )
     *
     * @psalm-var Collection<int, self>
     */
    private $readers;

    /**
     * @ORM\ManyToMany(targetEntity=UserWithSchema::class, mappedBy="readers")
     *
     * @psalm-var Collection<int, self>
     */
    private $authors;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setPassword(string $password): void
    {
        $this->password = md5($password);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    /** @psalm-return Collection<int, self> */
    public function getReaders(): Collection
    {
        return $this->readers;
    }

    /** @psalm-param Collection<int, self> $readers */
    public function setReaders($readers): self
    {
        $this->readers = $readers;

        return $this;
    }

    /** @psalm-return Collection<int, self> */
    public function getAuthors()
    {
        return $this->authors;
    }

    /** @param Collection<int, self> $authors */
    public function setAuthors($authors): self
    {
        $this->authors = $authors;

        return $this;
    }
}
