<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ORM\EntityManagerInterface;

final class MultipleTransactionORMExecutor extends AbstractExecutor
{
    use ORMExecutorCommon;

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false): void
    {
        $executor = $this;
        if ($append === false) {
            $this->em->wrapInTransaction(static function () use ($executor) {
                $executor->purge();
            });
        }

        foreach ($fixtures as $fixture) {
            $this->em->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixture) {
                $executor->load($em, $fixture);
            });
        }
    }
}
