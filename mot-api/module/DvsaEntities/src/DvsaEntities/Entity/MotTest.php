<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
 *  name="mot_test",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MotTestRepository")
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
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", fetch="EAGER", inversedBy="tests")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var \DvsaEntities\Entity\Colour
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="primary_colour_id", referencedColumnName="id")
     * })
     */
    private $primaryColour;

    /**
     * @var \DvsaEntities\Entity\Colour
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secondary_colour_id", referencedColumnName="id")
     * })
     */
    private $secondaryColour;

    /**
     * @var FuelType
     *
     * @ORM\ManyToOne(targetEntity="FuelType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tested_as_fuel_type_id", referencedColumnName="id")
     * })
     */
    private $fuelType;

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
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $registration;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $vin;

    /**
     * @var EmptyVrmReason
     *
     * @ORM\ManyToOne(targetEntity="EmptyVrmReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empty_vrm_reason_id", referencedColumnName="id")
     * })
     */
    private $emptyVrmReason;

    /**
     * @var EmptyVinReason
     *
     * @ORM\ManyToOne(targetEntity="EmptyVinReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empty_vin_reason_id", referencedColumnName="id")
     * })
     */
    private $emptyVinReason;

    /**
     * @var Make
     *
     * @ORM\ManyToOne(targetEntity="Make")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     * })
     */
    private $make;

    /**
     * @var string
     *
     * @ORM\Column(name="make_name", type="string", nullable=true)
     */
    private $makeName;

    /**
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id"),
     * })
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="model_name", type="string", nullable=true)
     */
    private $modelName;

    /**
     * @var CountryOfRegistration
     *
     * @ORM\ManyToOne(targetEntity="CountryOfRegistration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_of_registration_id", referencedColumnName="id")
     * })
     */
    private $countryOfRegistration;

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
     * @var \DvsaEntities\Entity\MotTestReasonForCancel
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTestReasonForCancel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_reason_for_cancel_id", referencedColumnName="id")
     * })
     */
    private $motTestReasonForCancel;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_for_termination_comment", type="string", length=240, nullable=true)
     */
    private $reasonForTerminationComment;

    /**
     * @var EnforcementFullPartialRetest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementFullPartialRetest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="full_partial_retest_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $fullPartialRetest;

    /**
     * @var Comment
     *
     * @ORM\JoinColumn(name="partial_reinspection_comment_id", referencedColumnName="id")
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     */
    protected $partialReinspectionComment;

    /**
     * @var Comment
     *
     * @ORM\JoinColumn(name="items_not_tested_comment_id", referencedColumnName="id")
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     */
    protected $itemsNotTestedComment;

    /**
     * @var string
     *
     * @ORM\Column(name="complaint_ref", type="string", length=30, nullable=true)
     */
    private $complaintRef;

    /**
     * @var OdometerReading
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\OdometerReading", cascade={"persist"})
     * @ORM\JoinColumn(name="odometer_reading_id", referencedColumnName="id")
     */
    private $odometerReading;

    /**
     * @var boolean
     *
     * @ORM\Column(name="private", type="smallint", nullable=false)
     */
    private $isPrivate;

    /**
     * @var integer
     *
     * @ORM\Column(name="document_id", type="integer", nullable=true)
     */
    private $document;

    /**
     * @var boolean
     *
     * @ORM\Column(name="one_person_test", type="smallint", nullable=true)
     */
    private $onePersonTest;

    /**
     * @var boolean
     * @ORM\Column(name="one_person_reinspection", type="smallint", nullable=true)
     */
    private $onePersonReInspection;

    /**
     * @var \DvsaEntities\Entity\EmergencyLog
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EmergencyLog")
     * @ORM\JoinColumn(name="emergency_log_id", referencedColumnName="id")
     */
    private $emergencyLog;

    /**
     * @var \DvsaEntities\Entity\EmergencyReason
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EmergencyReason")
     * @ORM\JoinColumn(name="emergency_reason_lookup_id", referencedColumnName="id")
     */
    private $emergencyReasonLookup;

    /**
     * @var Comment
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     * @ORM\JoinColumn(name="emergency_reason_comment_id", referencedColumnName="id", nullable=true)
     */
    private $emergencyReasonComment;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", nullable=true)
     */
    private $clientIp;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $version = 1;

    /**
     * @var \DvsaEntities\Entity\ModelDetail
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\ModelDetail")
     * @ORM\JoinColumn(name="model_detail_id", referencedColumnName="id")
     */
    private $modelDetail;
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
     * @param string $makeName
     *
     * @return $this
     */
    public function setFreeTextMakeName($makeName)
    {
        $this->makeName = $makeName;

        return $this;
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
     * @param Colour $primaryColour
     *
     * @return MotTest
     */
    public function setPrimaryColour($primaryColour)
    {
        $this->primaryColour = $primaryColour;

        return $this;
    }

    /**
     * @return Colour
     */
    public function getPrimaryColour()
    {
        return $this->primaryColour;
    }

    /**
     * @param Colour $secondaryColour
     *
     * @return MotTest
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;

        return $this;
    }

    /**
     * @return Colour
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param FuelType $fuelType
     *
     * @return MotTest
     */
    public function setFuelType(FuelType $fuelType = null)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * @return FuelType
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param \DvsaEntities\Entity\VehicleClass $vehicleClass
     *
     * @return MotTest
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
     * @param CountryOfRegistration $value
     *
     * @return MotTest
     */
    public function setCountryOfRegistration(CountryOfRegistration $value)
    {
        $this->countryOfRegistration = $value;

        return $this;
    }

    /**
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @param Make $make
     *
     * @return MotTest
     */
    public function setMake($make)
    {
        $this->make = $make;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Make
     */
    public function getMake()
    {
        return $this->make;
    }

    public function getMakeName()
    {
        return $this->make ? $this->make->getName() : $this->makeName;
    }

    /**
     * @param Model $model
     *
     * @return MotTest
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    public function getModelName()
    {
        return $this->model ? $this->model->getName() : $this->modelName;
    }

    public function setFreeTextModelName($modelName)
    {
        $this->modelName = $modelName;

        return $this;
    }

    /**
     * @param string $registration
     *
     * @return MotTest
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param string $vin
     *
     * @return MotTest
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
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
        $this->hasRegistration = (int) $hasRegistration;

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
                    return $rfr->getType() === $type;
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
     * Set MotTestReasonForCancel.
     *
     * @param \DvsaEntities\Entity\MotTestReasonForCancel $motTestReasonForCancel
     *
     * @return MotTest
     */
    public function setMotTestReasonForCancel(MotTestReasonForCancel $motTestReasonForCancel = null)
    {
        $this->motTestReasonForCancel = $motTestReasonForCancel;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTestReasonForCancel
     */
    public function getMotTestReasonForCancel()
    {
        return $this->motTestReasonForCancel;
    }

    /**
     * @return string
     */
    public function getReasonForTerminationComment()
    {
        return $this->reasonForTerminationComment;
    }

    /**
     * @param string $value
     *
     * @return MotTest
     */
    public function setReasonForTerminationComment($value)
    {
        $this->reasonForTerminationComment = $value;

        return $this;
    }

    /**
     * @return EnforcementFullPartialRetest
     */
    public function getFullPartialRetest()
    {
        return $this->fullPartialRetest;
    }

    /**
     * @param $fullPartialRetest
     *
     * @return MotTest
     */
    public function setFullPartialRetest($fullPartialRetest)
    {
        $this->fullPartialRetest = $fullPartialRetest;

        return $this;
    }

    /**
     * @param Comment $comment
     *
     * @return MotTest
     */
    public function setPartialReinspectionComment($comment)
    {
        $this->partialReinspectionComment = $comment;

        return $this;
    }

    /**
     * @return Comment
     */
    public function getPartialReinspectionComment()
    {
        return $this->partialReinspectionComment;
    }

    /**
     * @param Comment $comment
     *
     * @return MotTest
     */
    public function setItemsNotTestedComment($comment)
    {
        $this->itemsNotTestedComment = $comment;

        return $this;
    }

    /**
     * @return Comment
     */
    public function getItemsNotTestedComment()
    {
        return $this->itemsNotTestedComment;
    }

    /**
     * Set complaintRef.
     *
     * @param string $complaintRef
     *
     * @return MotTest
     */
    public function setComplaintRef($complaintRef)
    {
        $this->complaintRef = $complaintRef;

        return $this;
    }

    /**
     * Get complaintRef.
     *
     * @return string
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
     * @return OdometerReading
     */
    public function getOdometerReading()
    {
        return $this->odometerReading;
    }

    /**
     * @param OdometerReading $value
     *
     * @return MotTest
     */
    public function setOdometerReading($value)
    {
        $this->odometerReading = $value;

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
            if ($rfr->getType() === $rfrType) {
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
     * @param boolean $val
     *
     * @return MotTest
     */
    public function setIsPrivate($val)
    {
        $this->isPrivate = $val;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
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

    /**
     * @param int $onePersonTest
     *
     * @return MotTest
     */
    public function setOnePersonTest($onePersonTest)
    {
        $this->onePersonTest = $onePersonTest;

        return $this;
    }

    /**
     * @return int onePersonTest status
     */
    public function getOnePersonTest()
    {
        return $this->onePersonTest;
    }

    /**
     * @param int $onePersonReInspection
     *
     * @return MotTest
     */
    public function setonePersonReInspection($onePersonReInspection)
    {
        $this->onePersonReInspection = $onePersonReInspection;

        return $this;
    }

    /**
     * @return int onePersonReInspection status
     */
    public function getOnePersonReInspection()
    {
        return $this->onePersonReInspection;
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
     * @return mixed
     */
    public function getEmergencyLog()
    {
        return $this->emergencyLog;
    }

    /**
     * @param EmergencyLog $emergencyLog
     *
     * @return MotTest
     */
    public function setEmergencyLog($emergencyLog)
    {
        $this->emergencyLog = $emergencyLog;

        return $this;
    }

    /**
     * @return EmergencyLog
     */
    public function getEmergencyReasonComment()
    {
        return $this->emergencyReasonComment;
    }

    /**
     * @param mixed $emergencyReasonComment
     *
     * @return MotTest
     */
    public function setEmergencyReasonComment($emergencyReasonComment)
    {
        $this->emergencyReasonComment = $emergencyReasonComment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyReasonLookup()
    {
        return $this->emergencyReasonLookup;
    }

    /**
     * @param mixed $emergencyReasonLookup
     *
     * @return MotTest
     */
    public function setEmergencyReasonLookup($emergencyReasonLookup)
    {
        $this->emergencyReasonLookup = $emergencyReasonLookup;

        return $this;
    }
    /**
     * @param EmptyVrmReason $reason
     *
     * @return $this
     */
    public function setEmptyVrmReason($reason)
    {
        $this->emptyVrmReason = $reason;

        return $this;
    }

    /**
     * @return EmptyVrmReason
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * @param EmptyVinReason $reason
     *
     * @return $this
     */
    public function setEmptyVinReason($reason)
    {
        $this->emptyVinReason = $reason;

        return $this;
    }

    /**
     * @return EmptyVinReason
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
    }

    /**
     * @return ModelDetail
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @param ModelDetail $modelDetail
     *
     * @return MotTest
     */
    public function setModelDetail($modelDetail)
    {
        $this->modelDetail = $modelDetail;

        return $this;
    }
}
