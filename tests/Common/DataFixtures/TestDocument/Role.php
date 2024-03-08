<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestDocument;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class Role
{
    #[ODM\Id]
    private string|null $id = null;

    #[ODM\Field(type: 'string')]
    #[ODM\Index]
    private string|null $name = null;

    public function getId(): string|null
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string|null
    {
        return $this->name;
    }
}
