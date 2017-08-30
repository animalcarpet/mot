<?php

namespace DvsaEntities\Entity;

use DvsaEntities\EntityTrait\CommonIdentityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Test quality information test counts .
 *
 * @ORM\Table(
 *  name="tqi_test_count",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\TqiTestCountRepository")
 */
class TqiTestCount
{
    use CommonIdentityTrait;

        /**
     * @var \DateTime
     *
     * @ORM\Column(name="period_start_date", type="datetime", nullable=false)
     */
    private $periodStartDate;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\OneToOne(targetEntity="\DvsaEntities\Entity\Site", fetch="LAZY")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\OneToOne(targetEntity="\DvsaEntities\Entity\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    private $organisation;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="\DvsaEntities\Entity\Person", fetch="LAZY")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @var \DvsaEntities\Entity\VehicleClassGroup
     *
     * @ORM\OneToOne(targetEntity="\DvsaEntities\Entity\VehicleClassGroup", fetch="LAZY")
     * @ORM\JoinColumn(name="vehicle_class_group_id", referencedColumnName="id", nullable=false)
     */
    private $vehicleClassGroup;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_time", type="integer", length=10, nullable=true)
     */
    private $totalTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="failed_count", type="integer", length=10, nullable=true)
     */
    private $failedCount;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_count", type="integer", length=10, nullable=true)
     */
    private $totalCount;

    /**
     * @var integer sum of vehicles age (in months)
     *
     * @ORM\Column(name="vehicle_age_sum", type="integer", length=10, nullable=true)
     */
    private $vehicleAgeSum;

    /**
     * @var integer
     *
     * @ORM\Column(name="vehicles_with_manufacture_date_count", type="integer", length=10, nullable=true)
     */
    private $vehiclesWithManufactureDateCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;

    /**
     * @return \DateTime
     */
    public function getPeriodStartDate()
    {
        return $this->periodStartDate;
    }

    /**
     * @param \DateTime $periodStartDate
     * @return TqiTestCount
     */
    public function setPeriodStartDate(\DateTime $periodStartDate)
    {
        $this->periodStartDate = $periodStartDate;
        return $this;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     * @return TqiTestCount
     */
    public function setSite(Site $site = null)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     * @return TqiTestCount
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     * @return TqiTestCount
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return VehicleClassGroup
     */
    public function getVehicleClassGroup()
    {
        return $this->vehicleClassGroup;
    }

    /**
     * @param VehicleClassGroup $vehicleClassGroup
     * @return TqiTestCount
     */
    public function setVehicleClassGroup(VehicleClassGroup $vehicleClassGroup)
    {
        $this->vehicleClassGroup = $vehicleClassGroup;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * @param int $totalTime
     * @return TqiTestCount
     */
    public function setTotalTime(int $totalTime = null)
    {
        $this->totalTime = $totalTime;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedCount()
    {
        return $this->failedCount;
    }

    /**
     * @param int $failedCount
     * @return TqiTestCount
     */
    public function setFailedCount(int $failedCount = null)
    {
        $this->failedCount = $failedCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return TqiTestCount
     */
    public function setTotalCount(int $totalCount = null)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleAgeSum()
    {
        return $this->vehicleAgeSum;
    }

    /**
     * @param int $vehicleAgeSum
     * @return TqiTestCount
     */
    public function setVehicleAgeSum(int $vehicleAgeSum = null)
    {
        $this->vehicleAgeSum = $vehicleAgeSum;
        return $this;
    }

    /**
     * @return int
     */
    public function getVehiclesWithManufactureDateCount()
    {
        return $this->vehiclesWithManufactureDateCount;
    }

    /**
     * @param int $vehiclesWithManufactureDateCount
     * @return TqiTestCount
     */
    public function setVehiclesWithManufactureDateCount(int $vehiclesWithManufactureDateCount = null)
    {
        $this->vehiclesWithManufactureDateCount = $vehiclesWithManufactureDateCount;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     * @return TqiTestCount
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
        return $this;
    }
}