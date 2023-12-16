<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestValueObjects;

use JsonSerializable;

class Uuid implements JsonSerializable
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function jsonSerialize(): string
    {
        return $this->id;
    }

    public function __serialize(): array
    {
        return ['id' => $this->id];
    }

    public function __unserialize(array $data): void
    {
        ['id' => $this->id] = $data;
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
