<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestEntity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function md5;

/**
 * @ORM\Entity
 */
class UserRole
{
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\Id
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\Id
     */
    private $role;
    
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getRole()
    {
        return $this->role;
    }
}
