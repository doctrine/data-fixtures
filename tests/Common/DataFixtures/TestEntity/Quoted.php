<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table("`SELECT`")
 */
#[ORM\Entity]
#[ORM\Table(name: '`SELECT`')]
class Quoted
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    /** @ORM\Column(length=50, name="select") */
    #[ORM\Column(length: 50, name: '`SELECT`')]
    private ?string $select = null;

    /**
     * @ORM\ManyToMany(targetEntity=Quoted::class)
     * @ORM\JoinTable(name="`INSERT`",
     *      joinColumns={@ORM\JoinColumn(name="`SELECT`", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="`UPDATE`", referencedColumnName="id")}
     * )
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: '`INSERT`')]
    #[ORM\JoinColumn(name: '`SELECT`', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: '`UPDATE`', referencedColumnName: 'id')]
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

    public function getSelects(): ?Collection
    {
        return $this->selects;
    }

    public function setSelects(?Collection $selects): void
    {
        $this->selects = $selects;
    }
}
