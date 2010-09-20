<?php

namespace Doctrine\ORM;

use Closure;

class EntityManager
{
    public function transactional(Closure $func)
    {
        $func($this);
    }
}