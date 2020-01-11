<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid;

/**
 * @ORM\Entity
 */
class Link
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\Column(length=150)
     *
     * @var string
     */
    private $url;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
}
