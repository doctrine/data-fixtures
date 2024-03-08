<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestPurgeEntity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class IncludedEntity
{
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
