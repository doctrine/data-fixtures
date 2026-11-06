<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Sorter;

use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @internal this class is to be used only by data-fixtures internals: do not
 *           rely on it in your own libraries/applications. This class is
 *           designed to work with {@see TopologicalSorter}
 *           only.
 */
class Vertex
{
    public const int NOT_VISITED = 0;
    public const int IN_PROGRESS = 1;
    public const int VISITED     = 2;

    /** @phpstan-var self::* */
    public int $state = self::NOT_VISITED;

    /**
     * Map of node dependencies defined as hashes.
     *
     * @var string[]
     */
    public array $dependencyList = [];

    public function __construct(
        public ClassMetadata $value,
    ) {
    }
}
