<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

use Doctrine\ORM\EntityManagerInterface;

/**
 * ORMPurgerInterface
 */
interface ORMPurgerInterface
{
    /**
     * Set the EntityManagerInterface instance this purger instance should use.
     *
     * @return void
     */
    public function setEntityManager(EntityManagerInterface $em);
}
