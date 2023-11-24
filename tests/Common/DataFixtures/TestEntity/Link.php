<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid;

/** @ORM\Entity */
#[ORM\Entity]
class Link
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    /** @ORM\Column(length=150) */
    #[ORM\Column(length: 150)]
    private ?string $url = null;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
