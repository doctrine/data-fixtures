<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPCR\Util\NodeHelper;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
final class PHPCRPurger implements PurgerInterface
{
    public function __construct(private DocumentManagerInterface|null $dm = null)
    {
    }

    public function setDocumentManager(DocumentManager $dm): void
    {
        $this->dm = $dm;
    }

    public function getObjectManager(): DocumentManagerInterface|null
    {
        return $this->dm;
    }

    public function purge(): void
    {
        $session = $this->dm->getPhpcrSession();
        NodeHelper::purgeWorkspace($session);
        $session->save();
    }
}
