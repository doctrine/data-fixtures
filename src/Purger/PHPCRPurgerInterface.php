<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

/**
 * ORMPurgerInterface
 */
interface PHPCRPurgerInterface extends PurgerInterface
{
    /**
     * Set the DocumentManager instance this purger instance should use.
     */
    public function setDocumentManager(DocumentManager $dm): void;
}
