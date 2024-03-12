<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;

use function method_exists;

/**
 * Class responsible for executing data fixtures.
 */
final class PHPCRExecutor extends AbstractExecutor
{
    /**
     * @param DocumentManagerInterface $dm     manager instance used for persisting the fixtures
     * @param PHPCRPurger|null         $purger to remove the current data if append is false
     */
    public function __construct(private DocumentManagerInterface $dm, PHPCRPurger|null $purger = null)
    {
        parent::__construct($dm);

        if ($purger === null) {
            return;
        }

        $purger->setDocumentManager($dm);
        $this->setPurger($purger);
    }

    public function getObjectManager(): DocumentManagerInterface
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function execute(array $fixtures, bool $append = false): void
    {
        $that = $this;

        $function = static function ($dm) use ($append, $that, $fixtures): void {
            if ($append === false) {
                $that->purge();
            }

            foreach ($fixtures as $fixture) {
                $that->load($dm, $fixture);
            }
        };

        if (method_exists($this->dm, 'transactional')) {
            $this->dm->transactional($function);
        } else {
            $function($this->dm);
        }
    }
}
