<?php

declare(strict_types=1);

namespace Doctrine\Test\DataFixtures\Sorter;

use Doctrine\Common\DataFixtures\Sorter\Vertex;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Tests\Common\DataFixtures\BaseTest;

/**
 * @covers \Doctrine\Common\DataFixtures\Sorter\Vertex
 */
class VertexTest extends BaseTest
{
    public function testNode()
    {
        $value = new ClassMetadata('\Sample\Entity');
        $node  = new Vertex($value);

        self::assertSame($value, $node->value);
        self::assertSame(Vertex::NOT_VISITED, $node->state);
        self::assertEmpty($node->dependencyList);
    }
}
