<?php
namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Changes made at import from DVLA vehicles by tester.
 *
 * @ORM\Table(name="dvla_vehicle_import_change_log")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DvlaVehicleImportChangesRepository")
 */
class DvlaVehicleImportChangeLog extends Entity
{
    use CommonIdentityTrait;

    /**
     * @param string $colourCode
     *
     * @return $this
     */
    public function setColour($colourCode)
    {
        $this->colourCode = $colourCode;

        return $this;
    }

    /**
     * @return string colour code
     */
    public function getColour()
    {
        return $this->colourCode;
    }

    /**
     * @param string $fuelType
     *
     * @return $this
     */
    public function setFuelType($fuelType)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param string $secondaryColour
     *
     * @return $this
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param \DvsaEntities\Entity\Person $tester
     *
     * @return $this
     */
    public function setTester($tester)
    {
        $this->tester = $tester;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleClass $vehicleClass
     *
     * @return $this
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * @param \DateTime $imported
     *
     * @return $this
     */
    public function setImported($imported)
    {
        $this->imported = $imported;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getImported()
    {
        return $this->imported;
    }

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $tester;

    /**
     * @var integer
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     */
    private $vehicleId;

    /**
     * @param int $vehicleId
     *
     * @return $this
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * @var VehicleClass
     *
     * @ORM\ManyToOne(targetEntity="VehicleClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")
     * })
     */
    private $vehicleClass;

    /**
     * @var string
     *
     * @ORM\Column(name="main_colour_code", type="string", length=1, nullable=true)
     */
    private $colourCode;

    /**
     * @var string
     *
     * @ORM\Column(name="secondary_colour_code", type="string", length=1, nullable=true)
     */
    private $secondaryColour;

    /**
     * @var string
     *
     * @ORM\Column(name="fuel_type_code", type="string", length=2, nullable=true)
     */
    private $fuelType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="imported", type="datetime", nullable=false)
     */
    private $imported;
}
