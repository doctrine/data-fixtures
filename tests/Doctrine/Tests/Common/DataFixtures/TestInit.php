<?php

require_once __DIR__.'/../../../../../lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

use Doctrine\Common\ClassLoader;

$classLoader = new ClassLoader('Doctrine\\Common\\DataFixtures', __DIR__.'/../../../../../lib');
$classLoader->register();

$classLoader = new ClassLoader('Doctrine\\Tests\\Common\\DataFixtures', __DIR__.'/../../../../../tests');
$classLoader->register();

$classLoader = new ClassLoader('Doctrine\\Common', __DIR__.'/../../../../../lib/vendor/doctrine-common/lib');
$classLoader->register();

$classLoader = new ClassLoader('Doctrine\\DBAL', __DIR__.'/../../../../../lib/vendor/doctrine-dbal/lib');
$classLoader->register();

$classLoader = new ClassLoader('Doctrine\\ORM', __DIR__.'/../../../../../lib/vendor/doctrine-orm/lib');
$classLoader->register();
