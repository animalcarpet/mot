<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="visit",
 *            options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class Visit extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var \DateTime
     * @ORM\Column(name="visit_date", type="date", nullable=false)
     */
    private $visitDate;

    /**
     * @var \DvsaEntities\Entity\VisitReason
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\VisitReason", fetch="EAGER")
     * @ORM\JoinColumn(name="visit_reason_id", referencedColumnName="id")
     */
    private $visitReason;

    /**
     * @var \DvsaEntities\Entity\EnforcementVisitOutcome
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementVisitOutcome", fetch="EAGER")
     * @ORM\JoinColumn(name="visit_outcome_id", referencedColumnName="id")
     */
    private $visitOutcome;

    /**
     * @param \DvsaEntities\Entity\Site $vehicleTestingStation
     *
     * @return Visit
     */
    public function setVehicleTestingStation($vehicleTestingStation)
    {
        $this->vehicleTestingStation = $vehicleTestingStation;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Site
     */
    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }

    /**
     * @param \DateTime $visitDate
     *
     * @return Visit
     */
    public function setVisitDate($visitDate)
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementVisitOutcome $visitOutcome
     *
     * @return Visit
     */
    public function setVisitOutcome($visitOutcome)
    {
        $this->visitOutcome = $visitOutcome;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementVisitOutcome
     */
    public function getVisitOutcome()
    {
        return $this->visitOutcome;
    }

    /**
     * @param \DvsaEntities\Entity\VisitReason $visitReason
     *
     * @return Visit
     */
    public function setVisitReason($visitReason)
    {
        $this->visitReason = $visitReason;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\VisitReason
     */
    public function getVisitReason()
    {
        return $this->visitReason;
    }
}
