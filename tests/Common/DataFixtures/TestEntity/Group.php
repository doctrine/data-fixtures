<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Group
{
    #[ORM\Column]
    #[ORM\Id]
    private int|null $id = null;

    #[ORM\Column(length: 32)]
    #[ORM\Id]
    private string|null $code = null;

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
}
