<?php

declare(strict_types=1);

namespace Doctrine\Tests\Mock;

use Doctrine\ORM\EntityManagerInterface;

use function method_exists;

if (! method_exists(EntityManagerInterface::class, 'wrapInTransaction')) {
    interface ForwardCompatibleEntityManager extends EntityManagerInterface
    {
        public function wrapInTransaction(callable $func): mixed;
    }
} else {
    interface ForwardCompatibleEntityManager extends EntityManagerInterface
    {
    }
}
