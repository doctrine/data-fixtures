<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ORM\EntityManagerInterface;

interface ExecutorStrategy
{
    public function execute(EntityManagerInterface $em, iterable $fixtures, bool $append = false): void;
}
