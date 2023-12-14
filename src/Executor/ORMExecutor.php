<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class responsible for executing data fixtures.
 *
 * @final since 1.8.0
 */
class ORMExecutor extends AbstractExecutor
{
    use ORMExecutorCommon;

    /** @inheritDoc */
    public function execute(array $fixtures, bool $append = false)
    {
        $executor = $this;
        $this->em->wrapInTransaction(static function (EntityManagerInterface $em) use ($executor, $fixtures, $append) {
            if ($append === false) {
                $executor->purge();
            }

            foreach ($fixtures as $fixture) {
                $executor->load($em, $fixture);
            }
        });
    }
}
