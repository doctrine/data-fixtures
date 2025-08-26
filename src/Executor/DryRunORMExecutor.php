<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

/**
 * This executor allows to execute (and indirectly, print) SQL statements without
 * actually committing them to the database
 */
final class DryRunORMExecutor extends AbstractExecutor
{
    use ORMExecutorCommon;

    /** @inheritDoc */
    public function execute(array $fixtures, bool $append = false): void
    {
        $executor = $this;
        $this->em->beginTransaction();
        try {
            if ($append === false) {
                $executor->purge();
            }

            foreach ($fixtures as $fixture) {
                $executor->load($this->em, $fixture);
            }

            $this->em->flush();
        } finally {
            $this->em->rollBack();
            $this->em->close();
        }
    }
}
