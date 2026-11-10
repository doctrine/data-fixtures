<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestDocument;

use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\MappedSuperclass]
class AbstractRole
{
    #[ODM\Id]
    public string|null $id = null;
}
