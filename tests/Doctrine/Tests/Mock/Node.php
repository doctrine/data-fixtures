<?php

declare(strict_types=1);

namespace Doctrine\Tests\Mock;

/**
 * Node.
 */
class Node
{
    /** @var mixed */
    public $value;

    /** @param mixed $value */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
