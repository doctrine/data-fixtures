<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor\Strategy;

use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Executor\ExecutorStrategy;
use Doctrine\ORM\EntityManagerInterface;

final class MultipleTransactionsStrategy implements ExecutorStrategy
{
    /** @var AbstractExecutor */
    private $executor;

    public function __construct(AbstractExecutor $executor)
    {
        $this->executor = $executor;
    }

    public function execute(EntityManagerInterface $em, iterable $fixtures, bool $append = false): void
    {
        $executor = $this->executor;
        if ($append === false) {
            $em->wrapInTransaction(static function () use ($executor) {
                $executor->purge();
            });
        }

        foreach ($fixtures as $fixture) {
            $em->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixture) {
                $executor->load($em, $fixture);
            });
        }
    }
}
