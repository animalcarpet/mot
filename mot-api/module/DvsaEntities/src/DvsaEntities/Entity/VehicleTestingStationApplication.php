<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\UUIDIdentityTrait;

/**
 * VehicleTestingStationApplication.
 *
 * @ORM\Table(
 * name="application_site",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 * indexes={
 *  @ORM\Index(name="fk_vts_application_vts_details_id", columns={"application_site_details_id"}),
 *  @ORM\Index(name="fk_vts_application_status", columns={"status"}),
 *  @ORM\Index(
 * name="fk_vts_application_designated_manager_id",
 * columns={"application_authorised_examiner_designated_manager_id"})
 * }
 * )
 * @ORM\Entity
 */
class VehicleTestingStationApplication
{
    use UUIDIdentityTrait;

    const ENTITY_NAME = 'Vehicle Testing Station Application';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\OneToOne(
     * targetEntity="\DvsaEntities\Entity\Person",
     * cascade={"persist"},
     * fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     * name="application_authorised_examiner_designated_manager_id",
     * referencedColumnName="id",
     * nullable=false)
     * })
     */
    private $designatedManager;

    /**
     * @var \DvsaEntities\Entity\VehicleTestingStationDetails
     *
     * @ORM\OneToOne(
     * targetEntity="DvsaEntities\Entity\VehicleTestingStationDetails",
     * cascade={"persist"},
     * fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     * name="application_site_details_id",
     * referencedColumnName="id",
     * nullable=false)
     * })
     */
    private $vehicleTestingStationDetails;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submit_date_time", type="datetime", nullable=true)
     */
    private $submitDateTime;

    public function __construct()
    {
        $this->status = \DvsaCommon\Constants\ApplicationStatus::IN_PROGRESS;
    }

    /**
     * @param \DateTime $submitDateTime
     *
     * @return VehicleTestingStationApplication
     */
    public function setSubmitDateTime($submitDateTime = null)
    {
        $this->submitDateTime = $submitDateTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDateTime()
    {
        return $this->submitDateTime;
    }

    /**
     * @param string $status
     *
     * @return VehicleTestingStationApplication
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param VehicleTestingStationDetails $vehicleTestStationDetails
     *
     * @return VehicleTestingStationApplication
     */
    public function setVehicleTestingStationDetails($vehicleTestStationDetails)
    {
        $this->vehicleTestingStationDetails = $vehicleTestStationDetails;

        return $this;
    }

    /**
     * @return VehicleTestingStationDetails
     */
    public function getVehicleTestingStationDetails()
    {
        return $this->vehicleTestingStationDetails;
    }

    /**
     * @param \DvsaEntities\Entity\Person $designatedManager
     *
     * @return VehicleTestingStationApplication
     */
    public function setDesignatedManager($designatedManager)
    {
        $this->designatedManager = $designatedManager;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Person
     */
    public function getDesignatedManager()
    {
        return $this->designatedManager;
    }
}
