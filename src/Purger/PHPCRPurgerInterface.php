<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;

interface PHPCRPurgerInterface extends PurgerInterface
{
    /**
     * Set the DocumentManager instance this purger instance should use.
     */
    public function setDocumentManager(DocumentManagerInterface $dm): void;
}
