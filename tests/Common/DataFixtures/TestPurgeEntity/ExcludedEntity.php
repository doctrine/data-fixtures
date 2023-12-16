<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestPurgeEntity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
#[ORM\Entity]
class ExcludedEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    #[ORM\Column]
    #[ORM\Id]
    private int|null $id = null;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int|null
    {
        return $this->id;
    }
}
