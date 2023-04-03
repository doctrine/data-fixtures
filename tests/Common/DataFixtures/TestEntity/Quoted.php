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
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(length=50, name="select")
     *
     * @var string|null
     */
    private $select;

    /**
     * @ORM\ManyToMany(targetEntity=Quoted::class)
     * @ORM\JoinTable(name="`INSERT`",
     *      joinColumns={@ORM\JoinColumn(name="`SELECT`", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="`UPDATE`", referencedColumnName="id")}
     * )
     *
     * @var Collection|null
     */
    private $selects;

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
