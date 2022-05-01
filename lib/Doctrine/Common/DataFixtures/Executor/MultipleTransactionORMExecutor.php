<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ORM\EntityManagerInterface;

class MultipleTransactionORMExecutor implements Executor
{
    /** @var ORMExecutor */
    private $executor;

    public function __construct(ORMExecutor $executor)
    {
        $this->executor = $executor;
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false): void
    {
        $executor = $this->executor;
        if ($append === false) {
            $executor->getEntityManager()->wrapInTransaction(static function () use ($executor) {
                $executor->purge();
            });
        }

        foreach ($fixtures as $fixture) {
            $executor->getEntityManager()->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixture) {
                $executor->load($em, $fixture);
            });
        }
    }
}
