<?php


namespace Doctrine\Tests\Mock;

/**
 * Node.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class Node
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * Constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
