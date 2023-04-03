<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPCR\Util\NodeHelper;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 */
class PHPCRPurger implements PurgerInterface
{
    /** @var DocumentManagerInterface|null */
    private $dm;

    public function __construct(?DocumentManagerInterface $dm = null)
    {
        $this->dm = $dm;
    }

    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /** @return DocumentManagerInterface|null */
    public function getObjectManager()
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function purge()
    {
        $session = $this->dm->getPhpcrSession();
        NodeHelper::purgeWorkspace($session);
        $session->save();
    }
}
