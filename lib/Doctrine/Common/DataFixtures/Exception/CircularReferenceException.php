<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Exception;

use Doctrine\Common\CommonException;

class CircularReferenceException extends CommonException
{
}
