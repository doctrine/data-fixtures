<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestValueObjects;

use JsonSerializable;
use Serializable;

class Uuid implements JsonSerializable, Serializable
{
    /** @var string $id */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return $this->id;
    }

    public function serialize()
    {
        return $this->id;
    }

    public function unserialize($serialized)
    {
        $this->id = $serialized;
    }

    public function __toString()
    {
        return $this->id;
    }

    public static function unknown()
    {
        return new self('00000000-0000-0000-C000-000000000046');
    }
}
