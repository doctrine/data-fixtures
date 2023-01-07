<?php

declare(strict_types=1);

namespace Doctrine\Common\DataFixtures\Exception;

use LogicException;

class CircularReferenceException extends LogicException
{
}
