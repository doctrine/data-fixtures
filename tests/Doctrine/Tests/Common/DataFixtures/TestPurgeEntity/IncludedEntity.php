<?php

namespace Doctrine\Tests\Common\DataFixtures\TestPurgeEntity;

/**
 * 
 * @author Charles J. C. Elling, Jul 4, 2016
 * @Entity
 */
class IncludedEntity{
	
    /**
     * @Column(type="integer")
     * @Id
     */
    private $id;

    public function setId($id){
        $this->id = $id;
    }
    
	public function getId() {
		return $this->id;
	}
}