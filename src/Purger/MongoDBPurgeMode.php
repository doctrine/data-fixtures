<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Purger;

enum MongoDBPurgeMode: string
{
    /**
     * Purge the collections using deleteMany(). Don't create them.
     */
    case Delete = 'delete';

    /**
     * Drop the collections when purging, then recreate them.
     */
    case Drop = 'drop';
}
