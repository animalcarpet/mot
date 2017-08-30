<?php


namespace DvsaEntities\Entity;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Test quality information RFR counts .
 *
 * @ORM\Table(
 *  name="tqi_rfr_count",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\TqiRfrCountRepository")
 */
class TqiRfrCount
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
     * @ORM\Column(name="failed_count", type="integer", length=10, nullable=true)
     */
    private $failedCount;

    /**
     * @var integer
     *
     * @ORM\Column(name="test_item_category_id", type="integer", length=10, nullable=false)
     */
    private $testItemCategoryId;

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
     * @return TqiRfrCount
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
     * @return TqiRfrCount
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
     * @return TqiRfrCount
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
     * @return TqiRfrCount
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
     * @return TqiRfrCount
     */
    public function setVehicleClassGroup(VehicleClassGroup $vehicleClassGroup = null)
    {
        $this->vehicleClassGroup = $vehicleClassGroup;
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
     * @return TqiRfrCount
     */
    public function setFailedCount(int $failedCount = null)
    {
        $this->failedCount = $failedCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTestItemCategoryId()
    {
        return $this->testItemCategoryId;
    }

    /**
     * @param int $testItemCategoryId
     * @return TqiRfrCount
     */
    public function setTestItemCategoryId(int $testItemCategoryId)
    {
        $this->testItemCategoryId = $testItemCategoryId;
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
     * @return TqiRfrCount
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
        return $this;
    }
}