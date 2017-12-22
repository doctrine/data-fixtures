<?php

namespace Doctrine\Common\DataFixtures\Executor;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Common\DataFixtures\Purger\PHPCRPurger;

/**
 * Class responsible for executing data fixtures.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class PHPCRExecutor extends AbstractExecutor
{
    /**
     * Construct new fixtures loader instance.
     *
     * @param DocumentManager $dm DocumentManager instance used for persistence.
     */
    public function __construct(DocumentManager $dm, PHPCRPurger $purger = null)
    {
        $this->dm = $dm;
        if ($purger !== null) {
            $this->purger = $purger;
            $this->purger->setDocumentManager($dm);
        }
        parent::__construct($dm);
    }

    /**
     * Retrieve the DocumentManager instance this executor instance is using.
     *
     * @return \Doctrine\ODM\PHPCR\DocumentManager
     */
    public function getObjectManager()
    {
        return $this->dm;
    }

    /** @inheritDoc */
    public function execute(array $fixtures, $append = false)
    {
        $that = $this;

        $function = function ($dm) use ($append, $that, $fixtures) {
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

