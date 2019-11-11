<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Role
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(length=50)
     *
     * @var string|null
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
