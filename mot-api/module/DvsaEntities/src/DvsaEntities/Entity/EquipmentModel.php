<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Model.
 *
 * @ORM\Table(name="equipment_model")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EquipmentModel extends Entity
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
     * @var EquipmentMake
     *
     * @ORM\ManyToOne(targetEntity="EquipmentMake")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipment_make_id", referencedColumnName="id")
     * })
     */
    private $make;

    /**
     * @var EquipmentType
     *
     * @ORM\ManyToOne(targetEntity="EquipmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipment_type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var EquipmentModelStatus
     *
     * @ORM\ManyToOne(targetEntity="EquipmentModelStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipment_model_status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="software_version", type="string", nullable=true)
     */
    private $softwareVersion;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="certified", type="datetime", nullable=true)
     */
    private $certificationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="equipment_identification_number", type="string", nullable=true)
     */
    private $equipmentIdentificationNumber;

    /**
     * @var VehicleClass[]
     *
     * @ORM\ManyToMany(targetEntity="VehicleClass")
     * @ORM\JoinTable(
     *   name="equipment_model_vehicle_class_link",
     *   joinColumns={
     *      @ORM\JoinColumn(name="equipment_model_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *      @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id", unique=true)
     *   }
     * )
     */
    private $vehiclesClasses;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getMake()
    {
        return $this->make;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return EquipmentModelStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    /**
     * @return \DateTime|null
     */
    public function getCertificationDate()
    {
        return $this->certificationDate;
    }

    public function getEquipmentIdentificationNumber()
    {
        return $this->equipmentIdentificationNumber;
    }

    public function getVehiclesClasses()
    {
        return $this->vehiclesClasses;
    }
}
