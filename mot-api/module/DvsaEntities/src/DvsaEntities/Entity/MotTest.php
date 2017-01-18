<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaDocument\Entity\Document;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTest.
 *
 * @ORM\Table(
 *  name="mot_test_current",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MotTestHistoryRepository")
 */
class MotTest extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'MotTest';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $number;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $tester;

    /**
     * @var Vehicle
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Vehicle", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_id", referencedColumnName="id")
     * })
     */
    private $vehicle;

    /**
     * @var Int
     *
     * @ORM\Column(name="vehicle_version", type="integer", nullable=false)
     */
    private $vehicleVersion;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", fetch="EAGER", inversedBy="tests")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_registration", type="boolean", nullable=false)
     */
    private $hasRegistration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="started_date", type="datetime", nullable=true)
     */
    private $startedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed_date", type="datetime", nullable=true)
     */
    private $completedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issued_date", type="datetime", nullable=true)
     */
    private $issuedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="date", nullable=true)
     */
    private $expiryDate;

    /**
     * @var MotTestStatus
     *
     * @ORM\ManyToOne(targetEntity="MotTestStatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\MotTestReasonForRejection[]
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\MotTestReasonForRejection",
     *  mappedBy="motTest",
     *  fetch="EAGER",
     *  cascade={"persist"})
     */
    private $motTestReasonForRejections;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\BrakeTestResultClass3AndAbove",
     *  mappedBy="motTest",
     *  fetch="EAGER",
     *  cascade={"persist"})
     *
     * We ensure the latest is at the end of this collection by ordering on
     * this boolean field. It's mapped to the DB in the usual binary way (0=false).
     *
     * Any audit-type queries of this history ought to read the BrakeTestResultClass3AndAbove collection
     * separately to ensure the exact order is preserved (by using the audit columns).
     *
     * @ORM\OrderBy({"isLatest" = "ASC"})
     */
    private $brakeTestResultClass3AndAboveHistory;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\BrakeTestResultClass12",
     *  mappedBy="motTest",
     *  fetch="EAGER",
     *  cascade={"persist"})
     *
     * see comment on $brakeTestResultClass3AndAboveHistory
     * @ORM\OrderBy({"isLatest" = "ASC"})
     */
    private $brakeTestResultClass12History;

    /**
     * @var integer
     *
     * @ORM\Column(name="vehicle_weight", type="integer", length=10, nullable=true)
     */
    private $vehicleWeight;

    /**
     * @var WeightSource
     *
     * @ORM\ManyToOne(targetEntity="WeightSource")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_weight_source_lookup_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $vehicleWeightSource;

    /**
     * @var \DvsaEntities\Entity\MotTestType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTestType", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_type_id", referencedColumnName="id")
     * })
     */
    private $motTestType;

    /**
     * @var MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest")
     * @ORM\JoinColumn(name="mot_test_id_original", referencedColumnName="id")
     */
    private $motTestIdOriginal;

    /**
     * @var MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prs_mot_test_id", referencedColumnName="id")
     * })
     */
    private $prsMotTest;

    /**
     * @var MotTestCancelled
     *
     * @ORM\OneToOne(targetEntity="MotTestCancelled", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=true)
     */
    private $motTestCancelled;

    /**
     * @var MotTestComplaintRef
     *
     * @ORM\OneToOne(targetEntity="MotTestComplaintRef", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=true)
     */
    private $complaintRef;


    /**
     * @var string
     *
     * @ORM\Column(name="odometer_result_type", type="string", length=10, nullable=true)
     */
    private $odometerResultType;

    /**
     * @var string
     *
     * @ORM\Column(name="odometer_unit", type="string", length=2, nullable=true)
     */
    private $odometerUnit;

    /**
     * @var integer
     *
     * @ORM\Column(name="odometer_value", type="integer", length=11, nullable=true)
     */
    private $odometerValue;

    /**
     * @var integer
     *
     * @ORM\Column(name="document_id", type="integer", nullable=true)
     */
    private $document;

    /**
     * @var MotTestEmergencyReason
     *
     * @ORM\OneToOne(targetEntity="MotTestEmergencyReason", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=true)
     */
    private $motTestEmergencyReason;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", nullable=true)
     */
    private $clientIp;

    /**
     * @var Organisation
     *
     * @ORM\OneToOne(targetEntity="Organisation", fetch="EAGER")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    private $organisation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submitted_date", type="datetime", nullable=true)
     */
    private $submittedDate;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $version = 1;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->startedDate = new DateTime();

        $this->motTestReasonForRejections = new ArrayCollection();
        $this->brakeTestResultClass12History = new ArrayCollection();
        $this->brakeTestResultClass3AndAboveHistory = new ArrayCollection();
    }

    /**
     * @param \DvsaEntities\Entity\MotTestType $motTestType
     *
     * @return MotTest
     */
    public function setMotTestType($motTestType)
    {
        $this->motTestType = $motTestType;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTestType
     */
    public function getMotTestType()
    {
        return $this->motTestType;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param string $clientIp
     *
     * @return $this
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $value
     *
     * @return MotTest
     */
    public function setNumber($value)
    {
        $this->number = $value;

        return $this;
    }

    /**
     * Get MotTestIdOriginal.
     *
     * @return MotTest
     */
    public function getMotTestIdOriginal()
    {
        return $this->motTestIdOriginal;
    }

    /**
     * Set MotTestIdOriginal.
     *
     * @param MotTest $motTest
     *
     * @return MotTest
     */
    public function setMotTestIdOriginal(MotTest $motTest = null)
    {
        $this->motTestIdOriginal = $motTest;

        return $this;
    }

    /**
     * @param MotTest $prsMotTest
     *
     * @return MotTest
     */
    public function setPrsMotTest($prsMotTest)
    {
        $this->prsMotTest = $prsMotTest;

        return $this;
    }

    /**
     * @return MotTest
     */
    public function getPrsMotTest()
    {
        return $this->prsMotTest;
    }

    /**
     * Set Tester.
     *
     * @param Person $tester
     *
     * @return MotTest
     */
    public function setTester(Person $tester = null)
    {
        $this->tester = $tester;

        return $this;
    }

    /**
     * Get Tester.
     *
     * @return Person
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * Set Vehicle.
     *
     * @param Vehicle $vehicle
     *
     * @return MotTest
     */
    public function setVehicle(Vehicle $vehicle = null)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * Get Vehicle.
     *
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * @return Int
     */
    public function getVehicleVersion()
    {
        return $this->vehicleVersion;
    }

    /**
     * @param Int $vehicleVersion
     * @return MotTest
     */
    public function setVehicleVersion($vehicleVersion)
    {
        $this->vehicleVersion = $vehicleVersion;
        return $this;
    }

    /**
     * Set Site.
     *
     * @param Site $vehicleTestingStation
     *
     * @return MotTest
     */
    public function setVehicleTestingStation(Site $vehicleTestingStation = null)
    {
        $this->vehicleTestingStation = $vehicleTestingStation;

        return $this;
    }

    /**
     * Get Site.
     *
     * @return Site
     */
    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }

    /**
     * Proxy method
     * @return Colour
     */
    public function getPrimaryColour()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getColourDuringTest($this);
    }

    /**
     * Proxy method
     * @return Colour
     */
    public function getSecondaryColour()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getSecondaryColourDuringTest($this);
    }

    /**
     * Proxy method
     * @return FuelType
     */
    public function getFuelType()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getFuelTypeDuringTest($this);
    }

    /**
     * Proxy method
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClass()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getVehicleClassDuringTest($this);
    }

    /**
     * Proxy method
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getCountryOfRegistration();
    }

    /**
     * Proxy method
     * @return \DvsaEntities\Entity\Make
     */
    public function getMake()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getMake();
    }

    /**
     * Proxy method
     * @return Make|string
     */
    public function getMakeName()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getMakeName();
    }

    /**
     * Proxy method
     * @return Model
     */
    public function getModel()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getModel();
    }

    /**
     * Proxy method
     * @return Model|string
     */
    public function getModelName()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getModelName();
    }

    /**
     * Proxy method
     * @return string
     */
    public function getRegistration()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getRegistration();
    }

    /**
     * Proxy method
     * @return string
     */
    public function getVin()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getVin();
    }

    /**
     * Set hasRegistration.
     *
     * @param boolean $hasRegistration
     *
     * @return MotTest
     */
    public function setHasRegistration($hasRegistration)
    {
        $this->hasRegistration = (int)$hasRegistration;

        return $this;
    }

    /**
     * Get hasRegistration.
     *
     * @return boolean
     */
    public function getHasRegistration()
    {
        return $this->hasRegistration;
    }

    /**
     * Set startedDate.
     *
     * @param \DateTime $startedDate
     *
     * @return MotTest
     */
    public function setStartedDate($startedDate)
    {
        $this->startedDate = $startedDate;

        return $this;
    }

    /**
     * Get startedDate.
     *
     * @return \DateTime
     */
    public function getStartedDate()
    {
        return $this->startedDate;
    }

    /**
     * Set completedDate.
     *
     * @param \DateTime $completedDate
     *
     * @return MotTest
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;

        return $this;
    }

    /**
     * Get completedDate.
     *
     * @return \DateTime
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * Set issuedDate.
     *
     * @param \DateTime $issuedDate
     *
     * @return MotTest
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;

        return $this;
    }

    /**
     * Get issuedDate.
     *
     * @return \DateTime
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * Set expiryDate.
     *
     * @param \DateTime $expiryDate
     *
     * @return MotTest
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate.
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set status.
     *
     * @param MotTestStatus $status
     *
     * @return MotTest
     */
    public function setStatus(MotTestStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get MotTestStatus.
     * Proper accessor for the associated entity.
     *
     * @return MotTestStatus
     */
    public function getMotTestStatus()
    {
        return $this->status;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status->getName();
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
     * @return MotTest
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getSubmittedDate()
    {
        return $this->submittedDate;
    }

    /**
     * @param DateTime $submittedDate
     *
     * @return MotTest
     */
    public function setSubmittedDate(\DateTime $submittedDate)
    {
        $this->submittedDate = $submittedDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasFailures()
    {
        foreach ($this->getMotTestReasonForRejections() as $motTestReasonForRejection) {
            if ($motTestReasonForRejection->isFail() && true !== $motTestReasonForRejection->isMarkedAsRepaired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get MotTestReasonForRejections.
     *
     * @return \DvsaEntities\Entity\MotTestReasonForRejection[]
     */
    public function getMotTestReasonForRejections()
    {
        return $this->motTestReasonForRejections;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getMotTestReasonForRejectionsOfType($type)
    {
        return empty($this->motTestReasonForRejections)
            ? []
            :
            array_filter(
                $this->motTestReasonForRejections->toArray(),
                function (MotTestReasonForRejection $rfr) use ($type) {
                    return $rfr->getType()->getReasonForRejectionType() === $type;
                }
            );
    }

    /**
     * Adds to the BrakeTestResultClass3AndAbove history and sets previous brake tests to
     * "not latest". Setting to null has no effect.
     *
     * @param \DvsaEntities\Entity\BrakeTestResultClass3AndAbove $brakeTestResult
     *
     * @return MotTest
     */
    public function setBrakeTestResultClass3AndAbove(BrakeTestResultClass3AndAbove $brakeTestResult = null)
    {
        if ($brakeTestResult != null) {
            $brakeTestResult->setMotTest($this);
            $this->updateOldBrakeTestResults();
            $this->brakeTestResultClass3AndAboveHistory[] = $brakeTestResult;
        }

        return $this;
    }

    /**
     * Get BrakeTestResultClass3AndAbove.
     *
     * @return \DvsaEntities\Entity\BrakeTestResultClass3AndAbove
     */
    public function getBrakeTestResultClass3AndAbove()
    {
        $latestResult = $this->brakeTestResultClass3AndAboveHistory->last();

        return $latestResult ? $latestResult : null;
    }

    /**
     * Adds to the brakeTestResultClass12 history and sets previous brake tests to
     * "not latest". Setting to null has no effect.
     *
     * @param \DvsaEntities\Entity\BrakeTestResultClass12 $brakeTestResultClass12
     *
     * @return MotTest
     */
    public function setBrakeTestResultClass12(BrakeTestResultClass12 $brakeTestResultClass12 = null)
    {
        if ($brakeTestResultClass12 != null) {
            $brakeTestResultClass12->setMotTest($this);
            $this->updateOldBrakeTestResults();
            $this->brakeTestResultClass12History[] = $brakeTestResultClass12;
        }

        return $this;
    }

    /**
     * Get brakeTestResultClass12.
     *
     * @return \DvsaEntities\Entity\BrakeTestResultClass12
     */
    public function getBrakeTestResultClass12()
    {
        $latestResult = $this->brakeTestResultClass12History->last();

        return $latestResult ? $latestResult : null;
    }

    /**
     * @return int
     */
    public function getVehicleWeight()
    {
        return $this->vehicleWeight;
    }

    /**
     * @param int $vehicleWeight
     * @return MotTest
     */
    public function setVehicleWeight($vehicleWeight)
    {
        $this->vehicleWeight = $vehicleWeight;
        return $this;
    }

    /**
     * @return WeightSource
     */
    public function getVehicleWeightSource()
    {
        return $this->vehicleWeightSource;
    }

    /**
     * @param WeightSource $vehicleWeightSource
     * @return MotTest
     */
    public function setVehicleWeightSource($vehicleWeightSource)
    {
        $this->vehicleWeightSource = $vehicleWeightSource;
        return $this;
    }

    /**
     * Proxy method to try to fetch test's reason for cancel
     *
     * @return MotTestReasonForCancel|void
     */
    public function getMotTestReasonForCancel()
    {
        if (!$this->getMotTestCancelled()) {
            return;
        }

        try {
            return $this->getMotTestCancelled()->getMotTestReasonForCancel();
        } catch (EntityNotFoundException $e) {
            return;
        }
    }

    /**
     * @return MotTestCancelled
     */
    public function getMotTestCancelled()
    {
        return $this->motTestCancelled;
    }

    /**
     * @param MotTestCancelled $motTestCancelled
     * @return MotTest
     */
    public function setMotTestCancelled(MotTestCancelled $motTestCancelled)
    {
        $this->motTestCancelled = $motTestCancelled;
        $this->motTestCancelled->setId($this->getId());
        return $this;
    }

    /**
     * Proxy method to try to fetch test's reason for temination's comment
     *
     * @return string|void
     */
    public function getReasonForTerminationComment()
    {
        try {
            if ($this->getMotTestCancelled() && $this->getMotTestCancelled()->getComment()) {
                $comment = $this->getMotTestCancelled()->getComment();
                if ($comment instanceof Comment) {
                    return $comment->getComment();
                }
            }
        } catch (EntityNotFoundException $e) {
            return;
        }
    }

    /**
     * @param MotTestComplaintRef $complaintRef
     * @return $this
     */
    public function setComplaintRef(MotTestComplaintRef $complaintRef)
    {
        $this->complaintRef = $complaintRef;

        return $this;
    }

    /**
     * @return MotTestComplaintRef
     */
    public function getComplaintRef()
    {
        return $this->complaintRef;
    }

    /**
     * Add MotTestReasonForRejection.
     *
     * @param MotTestReasonForRejection $motTestReasonForRejection
     *
     * @return MotTest
     */
    public function addMotTestReasonForRejection(MotTestReasonForRejection $motTestReasonForRejection)
    {
        $this->motTestReasonForRejections[] = $motTestReasonForRejection;

        return $this;
    }

    /**
     * @param int $id
     */
    public function removeMotTestReasonForRejectionById($id)
    {
        /** @var MotTestReasonForRejection $rfrElement */
        foreach ($this->motTestReasonForRejections as $rfrElement) {
            if ($rfrElement->getId() === $id) {
                $this->motTestReasonForRejections->removeElement($rfrElement);
                break;
            }
        }
    }

    /**
     * @return string
     */
    public function getOdometerResultType()
    {
        return $this->odometerResultType;
    }

    /**
     * @param string $odometerResultType
     * @return MotTest
     */
    public function setOdometerResultType($odometerResultType)
    {
        $this->odometerResultType = $odometerResultType;
        return $this;
    }

    /**
     * @return string
     */
    public function getOdometerUnit()
    {
        return $this->odometerUnit;
    }

    /**
     * @param string $odometerUnit
     * @return MotTest
     */
    public function setOdometerUnit($odometerUnit)
    {
        $this->odometerUnit = $odometerUnit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOdometerValue()
    {
        return $this->odometerValue;
    }

    /**
     * @param int $odometerValue
     * @return MotTest
     */
    public function setOdometerValue($odometerValue)
    {
        $this->odometerValue = $odometerValue;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->getStatus() === MotTestStatusName::ACTIVE;
    }

    /**
     * @return boolean
     */
    public function isPassedOrFailed()
    {
        return $this->isPassed() || $this->isFailed();
    }

    /**
     * @return boolean
     */
    public function isPassed()
    {
        return $this->getStatus() === MotTestStatusName::PASSED;
    }

    /**
     * @return boolean
     */
    public function isFailed()
    {
        return $this->getStatus() === MotTestStatusName::FAILED;
    }

    /**
     * @return boolean
     */
    public function isCancelled()
    {
        return in_array(
            $this->getStatus(),
            [MotTestStatusName::ABANDONED, MotTestStatusName::ABORTED, MotTestStatusName::ABORTED_VE]
        );
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->getExpiryDate() < (new DateTime('now'));
    }

    /**
     * @param string $rfrType
     *
     * @return bool
     */
    public function hasRfrsOfType($rfrType)
    {
        foreach ($this->getMotTestReasonForRejections() as $rfr) {
            if ($rfr->getType()->getReasonForRejectionType() === $rfrType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getBrakeTestCount()
    {
        return count($this->brakeTestResultClass3AndAboveHistory) + count($this->brakeTestResultClass12History);
    }

    /**
     * @return bool
     */
    public function hasBrakeTestResults()
    {
        return $this->getBrakeTestResultClass3AndAbove() || $this->getBrakeTestResultClass12();
    }

    /**
     * @return bool|null
     */
    public function getBrakeTestGeneralPass()
    {
        $brakeTestResultClass12 = $this->getBrakeTestResultClass12();
        $brakeTestResultClass3AndAbove = $this->getBrakeTestResultClass3AndAbove();
        if ($brakeTestResultClass12) {
            return $brakeTestResultClass12->getGeneralPass();
        } elseif ($brakeTestResultClass3AndAbove) {
            return $brakeTestResultClass3AndAbove->getGeneralPass();
        } else {
            return null;
        }
    }

    /**
     * Extracts the RFRs for an MOT into an array, including mapping the
     * MOT Test type into the RFR.
     *
     * @param DoctrineObject $hydrator
     *
     * @deprecated use MotTestMapper
     *
     * @return array
     */
    public function extractRfrs(DoctrineObject $hydrator)
    {
        $results = [];

        $testType = $this->getMotTestType();

        foreach ($this->getMotTestReasonForRejections() as $key => $testRfr) {
            $results[$key] = $hydrator->extract($testRfr);
            if ($testRfr->getReasonForRejection() !== null) {
                $results[$key]['rfrId'] = $testRfr->getReasonForRejection()->getRfrId();

                // TODO remove duplication by using MotTestMapper
                foreach ($testRfr->getReasonForRejection()->getTestItemSelector()->getDescriptions() as $rfrCategoryDescription) {
                    if ($rfrCategoryDescription->getLanguage()->getCode() === LanguageTypeCode::ENGLISH) {
                        $results[$key]['name'] = $rfrCategoryDescription->getName();
                    }
                }
                foreach ($testRfr->getReasonForRejection()->getDescriptions() as $rfrDescription) {
                    if ($rfrDescription->getLanguage()->getCode() === LanguageTypeCode::ENGLISH) {
                        $results[$key]['failureText'] = $rfrDescription->getName();
                        $results[$key]['advisoryText'] = $rfrDescription->getAdvisoryText();
                    }
                }

                $results[$key]['type'] = $testRfr->getType()->getReasonForRejectionType();

                $results[$key]['inspectionManualReference'] =
                    $testRfr->getReasonForRejection()->getInspectionManualReference();
            } else {
                // TODO clean up after Manual Advisory problem would be solved (see VM-3386)
                $results[$key]['rfrId'] = null;
                $results[$key]['name'] = 'Manual Advisory';
                $results[$key]['nameCy'] = 'Cynghori Llawlyfr';
                $results[$key]['failureText'] = '';
                $results[$key]['failureTextCy'] = '';
                $results[$key]['inspectionManualReference'] = '';
            }
            $results[$key]['testType'] = $testType->getCode();

            /*
             * Deliberately added this item and raised a bug.
             *
             * Reason?
             *
             * 4 tickets currently blocked by new RBAC integration and cant have
             * any more. This is a one liner that can be fixed when we have the ability to detect what
             * type of person a person is.
             *
             * Apologies if this is wrong but I cant have any more stories blocked by this, they are piling up..
             *
             * Jira ticket:
             * https://jira.i-env.net/browse/VM-3673
             *
             */
            if ($testType->getCode() === MotTestTypeCode::TARGETED_REINSPECTION) {
                $results[$key]['personType'] = 'VE';
            } else {
                $results[$key]['personType'] = 'Tester';
            }
        }

        return $results;
    }

    /**
     * @param Document $document
     *
     * @return MotTest
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDocument()
    {
        return $this->document;
    }

    private function updateOldBrakeTestResults()
    {
        foreach ($this->brakeTestResultClass12History as $brakeTestResult) {
            $brakeTestResult->setIsLatest(false);
        }
        foreach ($this->brakeTestResultClass3AndAboveHistory as $brakeTestResult) {
            $brakeTestResult->setIsLatest(false);
        }
    }

    public function __clone()
    {
        $rfrsArray = $this->motTestReasonForRejections ? $this->motTestReasonForRejections->toArray() : [];
        $brakeTestHistoryCarsArray = $this->brakeTestResultClass3AndAboveHistory
            ? $this->brakeTestResultClass3AndAboveHistory->toArray() : [];
        $brakeTestHistoryBikesArray = $this->brakeTestResultClass12History
            ? $this->brakeTestResultClass12History->toArray() : [];
        $this->motTestReasonForRejections = new ArrayCollection($rfrsArray);
        $this->brakeTestResultClass3AndAboveHistory = new ArrayCollection($brakeTestHistoryCarsArray);
        $this->brakeTestResultClass12History = new ArrayCollection($brakeTestHistoryBikesArray);
    }

    /**
     * @param MotTestEmergencyReason $motTestEmergencyReason
     * @return $this
     */
    public function setMotTestEmergencyReason(MotTestEmergencyReason $motTestEmergencyReason = null)
    {
        $this->motTestEmergencyReason = $motTestEmergencyReason;
        return $this;
    }

    /**
     * @return MotTestEmergencyReason
     */
    public function getMotTestEmergencyReason()
    {
        return $this->motTestEmergencyReason;
    }

    /**
     * @return EmergencyLog
     */
    public function getEmergencyLog()
    {
        if (!$this->getMotTestEmergencyReason()) {
            return;
        }

        try {
            return $this->getMotTestEmergencyReason()->getEmergencyLog();
        } catch (EntityNotFoundException $e) {
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param EmergencyLog $emergencyLog
     * @return $this
     */
    public function setEmergencyLog(EmergencyLog $emergencyLog)
    {
        $this->getMotTestEmergencyReason()->setEmergencyLog($emergencyLog);
        return $this;
    }

    /**
     * @return EmergencyLog
     */
    public function getEmergencyReasonComment()
    {
        if (!$this->getMotTestEmergencyReason()) {
            return;
        }

        try {
            return $this->getMotTestEmergencyReason()->getComment();
        } catch (EntityNotFoundException $e) {
            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Comment $comment
     * @return $this
     */
    public function setEmergencyReasonComment(Comment $comment)
    {
        $this->getMotTestEmergencyReason()->setComment($comment);
        return $this;
    }

    /**
     * @return EmergencyReason
     */
    public function getEmergencyReasonLookup()
    {
        if (!$this->getMotTestEmergencyReason()) {
            return;
        }

        try {
            return $this->getMotTestEmergencyReason()->getEmergencyReason();
        } catch (EntityNotFoundException $e) {
            return;}
    }

    /**
     * @param EmergencyReason $emergencyReason
     * @return $this
     */
    public function setEmergencyReasonLookup(EmergencyReason $emergencyReason)
    {
        $this->getMotTestEmergencyReason()->setEmergencyReason($emergencyReason);
        return $this;
    }

    /**
     * Proxy method
     * @return EmptyVrmReason
     */
    public function getEmptyVrmReason()
    {
        if (!$this->getVehicle() || is_null($this->getVehicle()->getEmptyReasons())) {
            return;
        }

        return $this->getVehicle()->getEmptyReasons()->getEmptyVrmReason();
    }

    /**
     * Proxy method
     * @return EmptyVinReason
     */
    public function getEmptyVinReason()
    {
        if (!$this->getVehicle() || is_null($this->getVehicle()->getEmptyReasons())) {
            return;
        }

        return $this->getVehicle()->getEmptyReasons()->getEmptyVinReason();
    }

    /**
     * Proxy method
     * @return ModelDetail
     */
    public function getModelDetail()
    {
        if (!$this->getVehicle()) {
            return;
        }

        return $this->getVehicle()->getModelDetail();
    }
}
