<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`SELECT`')]
class Quoted
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int|null $id = null;

    #[ORM\Column(length: 50, name: '`SELECT`')]
    private string|null $select = null;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: '`INSERT`')]
    #[ORM\JoinColumn(name: '`SELECT`', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: '`UPDATE`', referencedColumnName: 'id')]
    private Collection|null $selects = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId(int|null $id): void
    {
        $this->id = $id;
    }

    public function getSelect(): string|null
    {
        return $this->select;
    }

    public function setSelect(string|null $select): void
    {
        $this->select = $select;
    }

    public function getSelects(): Collection|null
    {
        return $this->selects;
    }

    public function setSelects(Collection|null $selects): void
    {
        $this->selects = $selects;
    }
}
