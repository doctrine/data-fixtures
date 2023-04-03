<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("`SELECT`")
 */
class Quoted
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = null;

    /** @ORM\Column(length=50, name="select") */
    private ?string $select = null;

    /**
     * @ORM\ManyToMany(targetEntity=Quoted::class)
     * @ORM\JoinTable(name="`INSERT`",
     *      joinColumns={@ORM\JoinColumn(name="`SELECT`", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="`UPDATE`", referencedColumnName="id")}
     * )
     *
     * @var Collection|null
     */
    private ?Collection $selects = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getSelect(): ?string
    {
        return $this->select;
    }

    public function setSelect(?string $select): void
    {
        $this->select = $select;
    }

    /** @return Collection|null */
    public function getSelects(): ?Collection
    {
        return $this->selects;
    }

    /** @param Collection|null $selects */
    public function setSelects(?Collection $selects): void
    {
        $this->selects = $selects;
    }
}
