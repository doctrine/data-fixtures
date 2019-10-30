<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestPurgeEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class IncludedEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     *
     * @var int
     */
    private $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
