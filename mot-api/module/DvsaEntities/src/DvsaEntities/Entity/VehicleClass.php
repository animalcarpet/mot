<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * VehicleClass.
 *
 * @ORM\Table(
 *  name="vehicle_class",
 *  indexes={
 *      @ORM\Index(name="fk_vehicle_class_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_vehicle_class_last_updated_by", columns={"last_updated_by"})
 *  })
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\VehicleClassRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class VehicleClass extends Entity
{
    use CommonIdentityTrait;

    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_class_group_id", type="string", length=5, nullable=false)
     */
    private $group;

    public function __construct($code = null, $name = null)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->code;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGroup($value)
    {
        $this->group = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
}
