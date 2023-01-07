<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestPurgeEntity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class IncludedEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     *
     * @var int
     */
    private $id;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
