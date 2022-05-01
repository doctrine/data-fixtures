<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\FixtureInterface;

interface Executor
{
    /**
     * Executes the given array of data fixtures.
     *
     * @param FixtureInterface[] $fixtures Array of fixtures to execute.
     * @param bool               $append   Whether to append the data fixtures or purge the database before loading.
     */
    public function execute(array $fixtures, $append = false);
}
