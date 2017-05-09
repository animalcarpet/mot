<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * FuelType.
 *
 * @ORM\Table(
 *  name="fuel_type",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="uk_fuel_type_code", columns={"code"})
 *  },
 *  indexes={
 *      @ORM\Index(name="fk_fuel_type_person_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_fuel_type_person_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\FuelTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 *
 * TODO should be EnumType1 (now is mixing id with code)
 */
class FuelType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="dvla_propulsion_code", type="string", length=2, nullable=true)
     */
    private $dvlaPropulsionCode;

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
     * @return string
     */
    public function getDvlaPropulsionCode()
    {
        return $this->dvlaPropulsionCode;
    }

    /**
     * @param string $dvlaPropulsionCode
     *
     * @return $this
     */
    public function setDvlaPropulsionCode($dvlaPropulsionCode)
    {
        $this->dvlaPropulsionCode = $dvlaPropulsionCode;

        return $this;
    }
}
