<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity() */
class Group
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private ?int $id = null;

    /**
     * @ORM\Column(length=32)
     * @ORM\Id
     */
    private ?string $code = null;

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
}
