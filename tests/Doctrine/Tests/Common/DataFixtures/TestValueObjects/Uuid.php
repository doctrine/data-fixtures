<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestValueObjects;

use JsonSerializable;
use Serializable;

class Uuid implements JsonSerializable, Serializable
{
    /** @var string $id */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function jsonSerialize(): string
    {
        return $this->id;
    }

    public function serialize(): string
    {
        return $this->id;
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $this->id = $serialized;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public static function unknown(): self
    {
        return new self('00000000-0000-0000-C000-000000000046');
    }
}
