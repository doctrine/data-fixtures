<?php

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPCR\Util\NodeHelper;

/**
 * Class responsible for purging databases of data before reloading data fixtures.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class PHPCRPurger implements PurgerInterface
{
    /**
     * @var DocumentManagerInterface
     */
    private $dm;

    public function __construct(DocumentManagerInterface $dm = null)
    {
        $this->dm = $dm;
    }

    public function setDocumentManager(DocumentManagerInterface $dm)
    {
        $this->dm = $dm;
    }

    public function getObjectManager(): DocumentManagerInterface
    {
        return $this->dm;
    }

    /**
     * @inheritDoc
     */
    public function purge()
    {
        $session = $this->dm->getPhpcrSession();
        NodeHelper::purgeWorkspace($session);
        $session->save();
    }
}
