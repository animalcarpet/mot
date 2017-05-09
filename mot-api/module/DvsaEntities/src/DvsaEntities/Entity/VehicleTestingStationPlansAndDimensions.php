<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * VehicleTestingStationPlansAndDimensions.
 *
 * @ORM\Table(
 * name="application_site_plans_and_dimensions",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={
 *  @ORM\Index(
 * name="fk_vts_plans_and_dimensions_vts_details_id",
 * columns={"application_site_details_id"})
 * }
 * )
 * @ORM\Entity
 */
class VehicleTestingStationPlansAndDimensions
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationDetails
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationDetails",
     * cascade={"persist"},
     * fetch="LAZY",
     * inversedBy="vehicleTestingStationPlansAndDimensions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     * name="application_site_details_id",
     * referencedColumnName="id",
     * nullable=false
     * )
     * })
     */
    private $vehicleTestingStationDetails = '';

    /**
     * @var string
     *
     * @ORM\Column(name="plan_number", type="string", length=50, nullable=false)
     */
    private $planNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="plan_date", type="date", nullable=false)
     */
    private $planDate;

    /**
     * @var string
     *
     * @ORM\Column(name="drawing_number", type="string", length=50, nullable=false)
     */
    private $drawingNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="drawing_date", type="date", nullable=false)
     */
    private $drawingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="method_of_delivery", type="string", length=30, nullable=false)
     */
    private $methodOfDelivery;

    /**
     * @var bool
     *
     * @ORM\Column(name="plan_received", type="boolean", nullable=false)
     */
    private $sitePlanReceived = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="drawing_received", type="boolean", nullable=false)
     */
    private $drawingPlanReceived = false;

    /**
     * @param \DateTime $drawingDate
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function setDrawingDate($drawingDate)
    {
        $this->drawingDate = $drawingDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDrawingDate()
    {
        return $this->drawingDate;
    }

    /**
     * @param string $drawingNumber
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function setDrawingNumber($drawingNumber)
    {
        $this->drawingNumber = $drawingNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getDrawingNumber()
    {
        return $this->drawingNumber;
    }

    /**
     * @param \DateTime $planDate
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function setPlanDate($planDate)
    {
        $this->planDate = $planDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPlanDate()
    {
        return $this->planDate;
    }

    /**
     * @param string $planNumber
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function setPlanNumber($planNumber)
    {
        $this->planNumber = $planNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlanNumber()
    {
        return $this->planNumber;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleTestingStationDetails $vehicleTestingStationDetails
     *
     * @return \DvsaEntities\Entity\VehicleTestingStationPlansAndDimensions
     */
    public function setVehicleTestingStationDetails($vehicleTestingStationDetails)
    {
        $this->vehicleTestingStationDetails = $vehicleTestingStationDetails;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VehicleTestingStationDetails
     */
    public function getVehicleTestingStationDetails()
    {
        return $this->vehicleTestingStationDetails;
    }

    /**
     * @param string $methodOfDelivery
     *
     * @return VehicleTestingStationPlansAndDimensions
     */
    public function setMethodOfDelivery($methodOfDelivery)
    {
        $this->methodOfDelivery = $methodOfDelivery;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethodOfDelivery()
    {
        return $this->methodOfDelivery;
    }

    /**
     * @param bool $drawingReceived
     *
     * @return VehicleTestingStationPlansAndDimensions
     */
    public function setDrawingPlanReceived($drawingReceived)
    {
        $this->drawingPlanReceived = $drawingReceived;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDrawingPlanReceived()
    {
        return $this->drawingPlanReceived;
    }

    /**
     * @param bool $planReceived
     *
     * @return VehicleTestingStationPlansAndDimensions
     */
    public function setSitePlanReceived($planReceived)
    {
        $this->sitePlanReceived = $planReceived;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSitePlanReceived()
    {
        return $this->sitePlanReceived;
    }
}
