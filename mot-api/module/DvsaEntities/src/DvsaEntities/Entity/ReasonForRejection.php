<?php
namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommon\Utility\ArrayUtils;

/**
 * ReasonForRejection
 *
 * @ORM\Table(name="reason_for_rejection", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class ReasonForRejection
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $rfrId;

    /**
     * @var integer
     *
     * @ORM\Column(name="test_item_category_id", type="integer", nullable=false)
     * TODO map ManyToOne
     */
    private $testItemSelectorId;

    /**
     * @var string
     *
     * @ORM\Column(name="test_item_selector_name", type="string", length=100, nullable=false)
     */
    private $testItemSelectorName;

    /**
     * @var \Doctrine\Common\Collections\Collection|ReasonForRejectionDescription[]
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\ReasonForRejectionDescription",
     *  mappedBy="reasonForRejection",
     *  fetch="LAZY",
     *  cascade={"persist"}
     * )
     */
    private $descriptions;

    /**
     * @var string
     *
     * @ORM\Column(name="inspection_manual_reference", type="string", length=10, nullable=false)
     */
    private $inspectionManualReference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="minor_item", type="boolean", nullable=false)
     */
    private $minorItem;

    /**
     * @var boolean
     *
     * @ORM\Column(name="location_marker", type="boolean", nullable=false)
     */
    private $locationMarker;

    /**
     * @var boolean
     *
     * @ORM\Column(name="qt_marker", type="boolean", nullable=false)
     */
    private $qtMarker;

    /**
     * @var boolean
     *
     * @ORM\Column(name="note", type="boolean", nullable=false)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="manual", type="string", length=1, nullable=false)
     */
    private $manual;

    /**
     * @var boolean
     *
     * @ORM\Column(name="spec_proc", type="boolean", nullable=false)
     */
    private $specProc;

    /**
     * Owning side
     *
     * @ORM\ManyToMany(targetEntity="DvsaEntities\Entity\VehicleClass")
     * @ORM\JoinTable(name="rfr_vehicle_class_map",
     *      joinColumns={
     *          @ORM\JoinColumn(name="rfr_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $vehicleClasses;

    /**
     * @var \DvsaEntities\Entity\TestItemSelector
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestItemSelector")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="test_item_category_id", referencedColumnName="id")
     * })
     */
    private $testItemSelector;

    /**
     * @var \DvsaEntities\Entity\TestItemSelector
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestItemSelector")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="section_test_item_selector_id", referencedColumnName="id")
     * })
     */
    private $sectionTestItemSelector;

    public function __construct()
    {
        $this->vehicleClasses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_advisory", type="boolean", nullable=false)
     */
    private $isAdvisory;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_prs_fail", type="boolean", nullable=false)
     */
    private $isPrsFail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_be_dangerous", type="boolean", nullable=false)
     */
    private $canBeDangerous;

    /**
     * @var string
     *
     * @ORM\Column(name="audience", type="string", length=1, nullable=false)
     */
    private $audience;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * Set rfrId
     *
     * @param integer $rfrId
     * @return ReasonForRejection
     */
    public function setRfrId($rfrId)
    {
        $this->rfrId = $rfrId;

        return $this;
    }

    /**
     * Get rfrId
     *
     * @return integer
     */
    public function getRfrId()
    {
        return $this->rfrId;
    }

    /**
     * Get testItemSelectorId
     *
     * @return integer
     */
    public function getTestItemSelectorId()
    {
        return $this->testItemSelectorId;
    }

    /**
     * Set testItemSelectorName
     *
     * @param string $testItemSelectorName
     * @return ReasonForRejection
     */
    public function setTestItemSelectorName($testItemSelectorName)
    {
        $this->testItemSelectorName = $testItemSelectorName;

        return $this;
    }

    /**
     * Get testItemSelectorName
     *
     * @return string
     */
    public function getTestItemSelectorName()
    {
        return $this->testItemSelectorName;
    }

    /**
     * Set testItemSelectorNameCy
     *
     * @param string $testItemSelectorNameCy
     * @return ReasonForRejection
     */
    public function setTestItemSelectorNameCy($testItemSelectorNameCy)
    {
        $this->testItemSelectorNameCy = $testItemSelectorNameCy;

        return $this;
    }

    /**
     * Get testItemSelectorNameCy
     *
     * @return string
     */
    public function getTestItemSelectorNameCy()
    {
        return $this->testItemSelectorNameCy;
    }

    /**
     * Set inspectionManualReference
     *
     * @param string $inspectionManualReference
     * @return ReasonForRejection
     */
    public function setInspectionManualReference($inspectionManualReference)
    {
        $this->inspectionManualReference = $inspectionManualReference;

        return $this;
    }

    /**
     * Get inspectionManualReference
     *
     * @return string
     */
    public function getInspectionManualReference()
    {
        return $this->inspectionManualReference;
    }

    /**
     * Set minorItem
     *
     * @param boolean $minorItem
     * @return ReasonForRejection
     */
    public function setMinorItem($minorItem)
    {
        $this->minorItem = $minorItem;

        return $this;
    }

    /**
     * Get minorItem
     *
     * @return boolean
     */
    public function getMinorItem()
    {
        return $this->minorItem;
    }

    /**
     * Set locationMarker
     *
     * @param boolean $locationMarker
     * @return ReasonForRejection
     */
    public function setLocationMarker($locationMarker)
    {
        $this->locationMarker = $locationMarker;

        return $this;
    }

    /**
     * Get locationMarker
     *
     * @return boolean
     */
    public function getLocationMarker()
    {
        return $this->locationMarker;
    }

    /**
     * Set qtMarker
     *
     * @param boolean $qtMarker
     * @return ReasonForRejection
     */
    public function setQtMarker($qtMarker)
    {
        $this->qtMarker = $qtMarker;

        return $this;
    }

    /**
     * Get qtMarker
     *
     * @return boolean
     */
    public function getQtMarker()
    {
        return $this->qtMarker;
    }

    /**
     * Set note
     *
     * @param boolean $note
     * @return ReasonForRejection
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return boolean
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set manual
     *
     * @param string $manual
     * @return ReasonForRejection
     */
    public function setManual($manual)
    {
        $this->manual = $manual;

        return $this;
    }

    /**
     * Get manual
     *
     * @return string
     */
    public function getManual()
    {
        return $this->manual;
    }

    /**
     * Set specProc
     *
     * @param boolean $specProc
     * @return ReasonForRejection
     */
    public function setSpecProc($specProc)
    {
        $this->specProc = $specProc;

        return $this;
    }

    /**
     * Get specProc
     *
     * @return boolean
     */
    public function getSpecProc()
    {
        return $this->specProc;
    }

    /**
     * Set testItemSelector
     *
     * @param \DvsaEntities\Entity\TestItemSelector $testItemSelector
     * @return ReasonForRejection
     */
    public function setTestItemSelector(\DvsaEntities\Entity\TestItemSelector $testItemSelector = null)
    {
        $this->testItemSelector = $testItemSelector;

        return $this;
    }

    /**
     * Get testItemSelector
     *
     * @return \DvsaEntities\Entity\TestItemSelector
     */
    public function getTestItemSelector()
    {
        return $this->testItemSelector;
    }

    /**
     * Set sectionTestItemSelector
     *
     * @param \DvsaEntities\Entity\TestItemSelector $sectionTestItemSelector
     * @return ReasonForRejection
     */
    public function setSectionTestItemSelector(\DvsaEntities\Entity\TestItemSelector $sectionTestItemSelector = null)
    {
        $this->sectionTestItemSelector = $sectionTestItemSelector;

        return $this;
    }

    /**
     * Get sectionTestItemSelector
     *
     * @return \DvsaEntities\Entity\TestItemSelector
     */
    public function getSectionTestItemSelector()
    {
        return $this->sectionTestItemSelector;
    }

    /**
     * Add to vehicleClasses
     *
     * @param VehicleClass $vehicleClass
     *
     * @return $this
     */
    public function addVehicleClass(\DvsaEntities\Entity\VehicleClass $vehicleClass = null)
    {
        $this->vehicleClasses[] = $vehicleClass;

        return $this;
    }

    /**
     * Get vehicleClasses
     *
     * @return \Doctrine\Common\Collections\ArrayCollection()
     */
    public function getVehicleClasses()
    {
        return $this->vehicleClasses;
    }

    /**
     * @return boolean
     */
    public function getIsAdvisory()
    {
        return $this->isAdvisory;
    }

    /**
     * @return boolean
     */
    public function getIsPrsFail()
    {
        return $this->isPrsFail;
    }

    /**
     * @return boolean
     */
    public function getCanBeDangerous()
    {
        return $this->canBeDangerous;
    }

    /**
     * @param string $audience
     *
     * @return $this
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;
        return $this;
    }

    /**
     * @return string
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @return ArrayCollection|ReasonForRejectionDescription[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * @param  ArrayCollection|ReasonForRejectionDescription[] $descriptions
     *
     * @return $this
     */
    public function setDescriptions($descriptions)
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForTesterOnly()
    {
        return $this->getAudience() == ReasonForRejectionConstants::AUDIENCE_TESTER_CODE;
    }

    /**
     * @return bool
     */
    public function isForVehicleExaminerOnly()
    {
        return $this->getAudience() == ReasonForRejectionConstants::AUDIENCE_VEHICLE_EXAMINER_CODE;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @param VehicleClass $currentVehicleClass
     *
     * @return bool
     */
    public function isApplicableToVehicleClass(VehicleClass $currentVehicleClass)
    {
        return ArrayUtils::anyMatch(
            $this->getVehicleClasses(),
            function (VehicleClass $vehicleClass) use ($currentVehicleClass) {
                return $vehicleClass->getCode() === $currentVehicleClass->getCode();
            }
        );
    }
}
