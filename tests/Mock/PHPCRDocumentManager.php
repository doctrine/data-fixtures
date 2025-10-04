<?php

declare(strict_types=1);

namespace Doctrine\Tests\Mock;

use Closure;
use Doctrine\ODM\PHPCR\DocumentManager;

abstract class PHPCRDocumentManager extends DocumentManager
{
    abstract public function transactional(Closure $func): void;
}
