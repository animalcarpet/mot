<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Make.
 *
 * @ORM\Table(name="equipment_make")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EquipmentMake extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
