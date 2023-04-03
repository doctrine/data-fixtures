<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Sorter;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @internal this class is to be used only by data-fixtures internals: do not
 *           rely on it in your own libraries/applications. This class is
 *           designed to work with {@see \Doctrine\Common\DataFixtures\Sorter\TopologicalSorter}
 *           only.
 */
class Vertex
{
    public const NOT_VISITED = 0;
    public const IN_PROGRESS = 1;
    public const VISITED     = 2;

    /** @psalm-var self::* */
    public int $state = self::NOT_VISITED;

    /** Actual node value. */
    public ClassMetadata $value;

    /**
     * Map of node dependencies defined as hashes.
     *
     * @var string[]
     */
    public array $dependencyList = [];

    public function __construct(ClassMetadata $value)
    {
        $this->value = $value;
    }
}
