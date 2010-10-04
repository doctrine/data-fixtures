<?php

namespace Doctrine\Common\DataFixtures\Executor;

interface ExecutorInterface
{
    /**
     * Executes the given array of data fixtures.
     *
     * @param array $fixtures Array of fixtures to execute.
     * @param boolean $append Whether to append the data fixtures or purge the database before loading.
     */
    public function execute(array $fixtures, $append = false);
}