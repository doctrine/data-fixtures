<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Group
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

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode($code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
