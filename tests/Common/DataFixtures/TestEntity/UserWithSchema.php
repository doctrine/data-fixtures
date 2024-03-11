<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use function md5;

#[ORM\Entity]
#[ORM\Table(name: 'user', schema: 'test_schema')]
class UserWithSchema
{
    #[ORM\Column]
    #[ORM\Id]
    private int|null $id = null;

    #[ORM\Column(length: 32)]
    #[ORM\Id]
    private string|null $code = null;

    #[ORM\Column(length: 32)]
    private string|null $password = null;

    #[ORM\Column(length: 255)]
    private string|null $email = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private Role|null $role = null;

    /** @var Collection<int, self> */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'authors')]
    #[ORM\JoinTable(name: 'author_reader', schema: 'readers')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'reader_id', referencedColumnName: 'id')]
    private Collection $readers;

    /** @var Collection<int, self> */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'readers')]
    private Collection $authors;

    public function __construct()
    {
        $this->readers = new ArrayCollection();
        $this->authors = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): string|null
    {
        return $this->code;
    }

    public function setPassword(string $password): void
    {
        $this->password = md5($password);
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getRole(): Role|null
    {
        return $this->role;
    }

    /** @return Collection<int, self> */
    public function getReaders(): Collection
    {
        return $this->readers;
    }

    /**
     * @param Collection<int, self> $readers
     *
     * @return $this
     */
    public function setReaders(Collection $readers): self
    {
        $this->readers = $readers;

        return $this;
    }

    /** @return Collection<int, self> */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    /**
     * @param Collection<int, self> $authors
     *
     * @return $this
     */
    public function setAuthors(Collection $authors): self
    {
        $this->authors = $authors;

        return $this;
    }
}
