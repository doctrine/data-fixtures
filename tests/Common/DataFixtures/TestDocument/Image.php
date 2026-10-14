<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestDocument;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\File]
class Image
{
    #[ODM\Id]
    private string|null $id = null; // @phpstan-ignore property.unusedType (id is set by MongoDB ODM)

    public function getId(): string|null
    {
        return $this->id;
    }
}
