<?php

declare(strict_types=1);

namespace Common\DataFixtures\TestDocument;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\MappedSuperclass]
class AbstractRole
{
    #[ODM\Id]
    public string|null $id = null;
}
