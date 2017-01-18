<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommon\Dto\Common;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\FuelTypeDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;

class MotTestDto extends AbstractDataTransferObject
{
    /**
     * @var int
     */
    private $brakeTestCount;

    /**
     * @var array
     */
    private $brakeTestResult;

    /**
     * @var string
     */
    private $complaintRef;

    /**
     * @var string
     */
    private $completedDate;

    /**
     * @var CountryDto
     */
    private $countryOfRegistration;

    /**
     * @var int
     */
    private $document;

    /**
     * @var array
     */
    private $emergencyLog;

    /**
     * @var array
     */
    private $emergencyReasonComment;

    /**
     * @var array
     */
    private $emergencyReasonLookup;

    /**
     * @var string
     */
    private $expiryDate;

    /**
     * @var FuelTypeDto
     */
    private $fuelType;

    /**
     * @var array
     */
    private $fullPartialRetest;

    /**
     * @var bool
     */
    private $hasRegistration;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $isPrivate;

    /**
     * @var string
     */
    private $issuedDate;

    /**
     * @var array
     */
    private $itemsNotTestedComment;

    /**
     * @var string
     */
    private $make;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $motTestNumber;

    /**
     * @var string
     */
    private $odometerResultType;

    /**
     * @var string
     */
    private $odometerUnit;

    /**
     * @var integer
     */
    private $odometerValue;

    /**
     * @var int
     */
    private $onePersonReInspection;

    /**
     * @var int
     */
    private $onePersonTest;

    /**
     * @var array
     */
    private $partialReinspectionComment;

    /**
     * @var ColourDto
     */
    private $primaryColour;

    /**
     * @var string
     */
    private $reasonForTerminationComment;

    /**
     * @var array
     */
    private $reasonsForRejection;

    /**
     * @var string
     */
    private $registration;

    /**
     * @var int
     */
    private $emptyVrmReason;

    /**
     * @var ColourDto
     */
    private $secondaryColour;

    /**
     * @var string
     */
    private $startedDate;

    /**
     * @var string
     */
    private $status;

    /**
     * @var MotTestTypeDto
     */
    private $testType;

    /**
     * @var PersonDto
     */
    private $tester;

    /**
     * @var bool
     */
    private $testerBrakePerformanceNotTested;

    /**
     * @var VehicleDto
     */
    private $vehicle;

    /**
     * @var VehicleClassDto
     */
    private $vehicleClass;

    /**
     * @var array
     */
    private $vehicleTestingStation;

    /**
     * @var string
     */
    private $vin;

    /**
     * @var int
     */
    private $emptyVinReason;

    /**
     * @var array
     */
    private $pendingDetails;

    /**
     * @var ReasonForCancelDto
     */
    private $reasonForCancel;

    /**
     * @var MotTestDto
     */
    private $motTestOriginal;

    /**
     * @var string
     */
    private $prsMotTestNumber;

    /**
     * @var string
     */
    private $clientIp;

    /**
     * @var OrganisationDto
     */
    private $organisation;

    /**
     * @var string
     */
    private $submittedDate;

    /**
     * @return int
     */
    public function getBrakeTestCount()
    {
        return $this->brakeTestCount;
    }

    /**
     * @param int $brakeTestCount
     *
     * @return MotTestDto
     */
    public function setBrakeTestCount($brakeTestCount)
    {
        $this->brakeTestCount = $brakeTestCount;

        return $this;
    }

    /**
     * @return array
     */
    public function getBrakeTestResult()
    {
        return $this->brakeTestResult;
    }

    /**
     * @param array $brakeTestResult
     *
     * @return MotTestDto
     */
    public function setBrakeTestResult($brakeTestResult)
    {
        $this->brakeTestResult = $brakeTestResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getComplaintRef()
    {
        return $this->complaintRef;
    }

    /**
     * @param string $complaintRef
     *
     * @return MotTestDto
     */
    public function setComplaintRef($complaintRef)
    {
        $this->complaintRef = $complaintRef;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * @param string $completedDate
     *
     * @return MotTestDto
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;

        return $this;
    }

    /**
     * @return CountryDto
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @param CountryDto $countryOfRegistration
     *
     * @return MotTestDto
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;

        return $this;
    }

    /**
     * This method returns the ID of the jasper data snapshot row.
     *
     * @return int
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param int $document The ID of the jasper snapshot data ID
     *
     * @return MotTestDto
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmergencyLog()
    {
        return $this->emergencyLog;
    }

    /**
     * @param array $emergencyLog
     *
     * @return MotTestDto
     */
    public function setEmergencyLog($emergencyLog)
    {
        $this->emergencyLog = $emergencyLog;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmergencyReasonComment()
    {
        return $this->emergencyReasonComment;
    }

    /**
     * @param array $emergencyReasonComment
     *
     * @return MotTestDto
     */
    public function setEmergencyReasonComment($emergencyReasonComment)
    {
        $this->emergencyReasonComment = $emergencyReasonComment;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmergencyReasonLookup()
    {
        return $this->emergencyReasonLookup;
    }

    /**
     * @param array $emergencyReasonLookup
     *
     * @return MotTestDto
     */
    public function setEmergencyReasonLookup($emergencyReasonLookup)
    {
        $this->emergencyReasonLookup = $emergencyReasonLookup;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param string $expiryDate
     *
     * @return MotTestDto
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * @return FuelTypeDto
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param FuelTypeDto $fuelType
     *
     * @return MotTestDto
     */
    public function setFuelType($fuelType)
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasRegistration()
    {
        return $this->hasRegistration;
    }

    /**
     * @param boolean $hasRegistration
     *
     * @return MotTestDto
     */
    public function setHasRegistration($hasRegistration)
    {
        $this->hasRegistration = $hasRegistration;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MotTestDto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @param int $isPrivate
     *
     * @return MotTestDto
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    /**
     * @return string
     */
    public function getIssuedDate()
    {
        if ($this->issuedDate) {
            return $this->issuedDate;
        }

        if ($this->completedDate) {
            return $this->completedDate;
        }

        return $this->startedDate;
    }

    /**
     * @param string $issuedDate
     *
     * @return MotTestDto
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;

        return $this;
    }

    public function getMake()
    {
        return $this->make;
    }

    public function setMake($make)
    {
        $this->make = $make;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getMotTestNumber()
    {
        return $this->motTestNumber;
    }

    /**
     * @param string $motTestNumber
     *
     * @return MotTestDto
     */
    public function setMotTestNumber($motTestNumber)
    {
        $this->motTestNumber = $motTestNumber;

        return $this;
    }

    /**
     * Proxy method
     * @param OdometerReadingDto $odometerReadingDto
     * @return $this
     */
    public function setOdometerReading(OdometerReadingDto $odometerReadingDto)
    {
        $this->setOdometerValue($odometerReadingDto->getValue());
        $this->setOdometerUnit($odometerReadingDto->getUnit());
        $this->setOdometerResultType($odometerReadingDto->getResultType());
        return $this;
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
     * @return MotTestDto
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
     * @return MotTestDto
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
     * @return MotTestDto
     */
    public function setOdometerValue($odometerValue)
    {
        $this->odometerValue = $odometerValue;
        return $this;
    }

    /**
     * @return int
     */
    public function getOnePersonReInspection()
    {
        return $this->onePersonReInspection;
    }

    /**
     * @param int $onePersonReInspection
     *
     * @return MotTestDto
     */
    public function setOnePersonReInspection($onePersonReInspection)
    {
        $this->onePersonReInspection = $onePersonReInspection;

        return $this;
    }

    /**
     * @return int
     */
    public function getOnePersonTest()
    {
        return $this->onePersonTest;
    }

    /**
     * @param int $onePersonTest
     *
     * @return MotTestDto
     */
    public function setOnePersonTest($onePersonTest)
    {
        $this->onePersonTest = $onePersonTest;

        return $this;
    }

    /**
     * @return ColourDto
     */
    public function getPrimaryColour()
    {
        return $this->primaryColour;
    }

    /**
     * @param ColourDto $primaryColour
     *
     * @return MotTestDto
     */
    public function setPrimaryColour($primaryColour)
    {
        $this->primaryColour = $primaryColour;

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonForTerminationComment()
    {
        return $this->reasonForTerminationComment;
    }

    /**
     * @param string $reasonForTerminationComment
     *
     * @return MotTestDto
     */
    public function setReasonForTerminationComment($reasonForTerminationComment)
    {
        $this->reasonForTerminationComment = $reasonForTerminationComment;

        return $this;
    }

    /**
     * @return array
     */
    public function getReasonsForRejection()
    {
        return $this->reasonsForRejection;
    }

    /**
     * @return array
     */
    public function getReasonsForRejectionExcludingRepairedDefects()
    {
        $reasonsForRejection = $this->reasonsForRejection;

        // Defects that are flagged as "markedAsRepaired" are removed from the ReasonsForRejection list
        foreach (array_keys($reasonsForRejection) as $type) {
            foreach (array_keys($reasonsForRejection[$type]) as $k) {
                if (isset($reasonsForRejection[$type][$k]['markedAsRepaired'])
                    && true === $reasonsForRejection[$type][$k]['markedAsRepaired']) {
                    unset($reasonsForRejection[$type][$k]);
                }
            }
        }

        return $reasonsForRejection;
    }

    /**
     * @param array $reasonsForRejection
     *
     * @return MotTestDto
     */
    public function setReasonsForRejection($reasonsForRejection)
    {
        $this->reasonsForRejection = $reasonsForRejection;

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
     * @param string $registration
     *
     * @return MotTestDto
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * @return ColourDto
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param ColourDto $secondaryColour
     *
     * @return MotTestDto
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartedDate()
    {
        return $this->startedDate;
    }

    /**
     * @param string $startedDate
     *
     * @return MotTestDto
     */
    public function setStartedDate($startedDate)
    {
        $this->startedDate = $startedDate;

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
     * @param string $status
     *
     * @return MotTestDto
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return MotTestTypeDto
     */
    public function getTestType()
    {
        return $this->testType;
    }

    /**
     * @param MotTestTypeDto $testType
     *
     * @return MotTestDto
     */
    public function setTestType($testType)
    {
        $this->testType = $testType;

        return $this;
    }

    /**
     * @return PersonDto
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * @param PersonDto $tester
     *
     * @return MotTestDto
     */
    public function setTester($tester)
    {
        $this->tester = $tester;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getTesterBrakePerformanceNotTested()
    {
        return $this->testerBrakePerformanceNotTested;
    }

    /**
     * @param boolean $testerBrakePerformanceNotTested
     *
     * @return MotTestDto
     */
    public function setTesterBrakePerformanceNotTested($testerBrakePerformanceNotTested)
    {
        $this->testerBrakePerformanceNotTested = $testerBrakePerformanceNotTested;

        return $this;
    }

    /**
     * @return VehicleDto
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * @param VehicleDto $vehicle
     *
     * @return MotTestDto
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @return VehicleClassDto
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * @param VehicleClassDto $vehicleClass
     *
     * @return MotTestDto
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }

    /**
     * @param array $vehicleTestingStation
     *
     * @return MotTestDto
     */
    public function setVehicleTestingStation($vehicleTestingStation)
    {
        $this->vehicleTestingStation = $vehicleTestingStation;

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
     * @param string $vin
     *
     * @return MotTestDto
     */
    public function setVin($vin)
    {
        $this->vin = $vin;

        return $this;
    }

    /**
     * @return array
     */
    public function getPendingDetails()
    {
        return $this->pendingDetails;
    }

    /**
     * @param array $pendingDetails
     *
     * @return MotTestDto
     */
    public function setPendingDetails($pendingDetails)
    {
        $this->pendingDetails = $pendingDetails;

        return $this;
    }

    /**
     * @return ReasonForCancelDto
     */
    public function getReasonForCancel()
    {
        return $this->reasonForCancel;
    }

    /**
     * @param ReasonForCancelDto $reasonForCancel
     *
     * @return MotTestDto
     */
    public function setReasonForCancel($reasonForCancel)
    {
        $this->reasonForCancel = $reasonForCancel;

        return $this;
    }

    /**
     * @return MotTestDto
     */
    public function getMotTestOriginal()
    {
        return $this->motTestOriginal;
    }

    /**
     * @param MotTestDto $motTestOriginal
     *
     * @return MotTestDto
     */
    public function setMotTestOriginal($motTestOriginal)
    {
        $this->motTestOriginal = $motTestOriginal;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrsMotTestNumber()
    {
        return $this->prsMotTestNumber;
    }

    /**
     * @param string $prsMotTestNumber
     *
     * @return MotTestDto
     */
    public function setPrsMotTestNumber($prsMotTestNumber)
    {
        $this->prsMotTestNumber = $prsMotTestNumber;

        return $this;
    }

    /**
     * @param string $reasonCode
     *
     * @return $this
     */
    public function setEmptyVrmReason($reasonCode)
    {
        $this->emptyVrmReason = $reasonCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * @param string $reasonCode
     *
     * @return $this
     */
    public function setEmptyVinReason($reasonCode)
    {
        $this->emptyVinReason = $reasonCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
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
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @return OrganisationDto
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param OrganisationDto $organisation
     *
     * @return MotTestDto
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubmittedDate()
    {
        return $this->submittedDate;
    }

    /**
     * @param \DateTime $submittedDate
     *
     * @return MotTestDto
     */
    public function setSubmittedDate($submittedDate)
    {
        $this->submittedDate = $submittedDate;

        return $this;
    }
}
