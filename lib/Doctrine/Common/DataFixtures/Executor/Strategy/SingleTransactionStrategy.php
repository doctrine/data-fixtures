<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor\Strategy;

use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Executor\ExecutorStrategy;
use Doctrine\ORM\EntityManagerInterface;

final class SingleTransactionStrategy implements ExecutorStrategy
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
        $em->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixtures, $append) {
            if ($append === false) {
                $executor->purge();
            }

            foreach ($fixtures as $fixture) {
                $executor->load($em, $fixture);
            }
        });
    }
}
