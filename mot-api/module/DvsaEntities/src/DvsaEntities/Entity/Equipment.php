<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Equipment.
 *
 * @ORM\Table(name="equipment")
 * @ORM\Entity
 */
class Equipment extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var EquipmentModel
     *
     * @ORM\ManyToOne(targetEntity="EquipmentModel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipment_model_id", referencedColumnName="id")
     * })
     */
    private $equipmentModel;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", fetch="LAZY", inversedBy="equipments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var string
     *
     * @ORM\Column(name="serial_number", type="string", length=50, nullable=false)
     */
    private $serialNumber;

    /**
     * @var EquipmentStatus
     *
     * @ORM\ManyToOne(targetEntity="EquipmentStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipment_status_id", referencedColumnName="id")
     * })
     */
    private $status;

    public function __construct($serialNumber)
    {
        $this->dateAdded = new \DateTime();
        $this->serialNumber = $serialNumber;
    }

    /**
     * @return EquipmentModel
     */
    public function getEquipmentModel()
    {
        return $this->equipmentModel;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * @return EquipmentStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
