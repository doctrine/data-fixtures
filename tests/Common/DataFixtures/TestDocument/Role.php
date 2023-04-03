<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestDocument;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Role
{
    /** @ODM\Id */
    private ?string $id = null;

    /**
     * @ODM\Field(type="string")
     * @ODM\Index
     */
    private ?string $name = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
