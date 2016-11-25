<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Log\LoggerInterface;

/**
 * Class VehicleSearchResult
 *
 * @package DvsaMotTest\Model
 */
class VehicleSearchResult
{

    const NOT_TESTED = 'Not tested';

    /** @var string $id */
    private $id;

    /** @var string $registrationNumber */
    private $registrationNumber;

    /** @var string $vin */
    private $vin;

    /** @var string $make */
    private $make;

    /** @var string $model */
    private $model;

    /** @var string $modelDetail */
    private $modelDetail;

    /** @var string $fuelType */
    private $fuelType;

    /** @var string $motTestCount */
    private $motTestCount;

    /** @var string $lastMotTestDate */
    private $lastMotTestDate;

    /** @var string $retestEligibility */
    private $retestEligibility;

    /** @var array $results */
    private $results;

    /** @var bool $isDvlaVehicle */
    private $isDvlaVehicle;

    /** @var ParamObfuscator */
    protected $paramObfuscator;

    /** @var VehicleSearchSource */
    protected $vehicleSearchSoruce;

    /** @var boolean */
    protected $isIncognito;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param ParamObfuscator $paramObfuscator
     * @param VehicleSearchSource $vehicleSearchSource
     * @param LoggerInterface $logger
     */
    public function __construct(ParamObfuscator $paramObfuscator, VehicleSearchSource $vehicleSearchSource, LoggerInterface $logger)
    {
        $this->paramObfuscator = $paramObfuscator;
        $this->vehicleSearchSoruce = $vehicleSearchSource;
        $this->logger = $logger;
    }

    /**
     * @param array $apiVehicleData
     * @return $this|VehicleSearchResult
     */
    public function addResults(array $apiVehicleData)
    {
        if (empty($apiVehicleData)) {
            return new self($this->paramObfuscator, $this->vehicleSearchSoruce, $this->logger);
        }

        foreach ($apiVehicleData as $vehicleData) {
            $newVehicleObject = new self($this->paramObfuscator, $this->vehicleSearchSoruce, $this->logger);
            $newVehicleObject->setId(ArrayUtils::tryGet($vehicleData, 'id'));
            $newVehicleObject->setRegistrationNumber(ArrayUtils::tryGet($vehicleData, 'registration'));
            $newVehicleObject->setVin(ArrayUtils::tryGet($vehicleData, 'vin'));
            $newVehicleObject->setMake(ArrayUtils::tryGet($vehicleData, 'make'));
            $newVehicleObject->setModel(ArrayUtils::tryGet($vehicleData, 'model'));
            $newVehicleObject->setModelDetail(ArrayUtils::tryGet($vehicleData, 'modelDetail'));

            if (isset($vehicleData['fuelType'])) {
                $newVehicleObject->setFuelType(ArrayUtils::tryGet($vehicleData['fuelType'], 'name'));
            }

            $newVehicleObject->setMotTestCount(ArrayUtils::tryGet($vehicleData, 'total_mot_tests', 0));
            $newVehicleObject->setLastMotTestDate(ArrayUtils::tryGet($vehicleData, 'mot_completed_date'));
            $newVehicleObject->setIsDvlaVehicle(ArrayUtils::tryGet($vehicleData, 'isDvla'));
            $newVehicleObject->setRetestEligibility(ArrayUtils::tryGet($vehicleData, 'retest_eligibility'));
            $newVehicleObject->setIsIncognito(ArrayUtils::tryGet($vehicleData, 'isIncognito'));
            if (null === $newVehicleObject->isIncognito()) {
                $newVehicleObject->setIsIncognito(false);
                $errorMessage = sprintf('An error occurred while trying to get the isIncognito field for vehicle with Reg: %s and VIN: %s', $newVehicleObject->getRegistrationNumber(), $newVehicleObject->getVin());
                $this->logger->err($errorMessage);
            }

            $this->addResult($newVehicleObject);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getObfuscatedId()
    {
        return $this->paramObfuscator->obfuscate($this->getId());
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * @param string $registrationNumber
     */
    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;
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
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        return $this;
    }

    /**
     * @return string
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param string $make
     */
    public function setMake($make)
    {
        $this->make = $make;
        return $this;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param bool $isIncognito
     */
    public function setIsIncognito($isIncognito)
    {
        $this->isIncognito = $isIncognito;
        return $this;
    }

    /**
     * @return string
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @param string $modelDetail
     */
    public function setModelDetail($modelDetail)
    {
        $this->modelDetail = $modelDetail;
    }

    /**
     * @return string
     */
    public function getMakeAndModel()
    {
        return $this->getMake() . ' ' . $this->getModel();
    }

    /**
     * @return string
     */
    public function getFuelType()
    {
        return $this->fuelType;
    }

    /**
     * @param string $fuelType
     */
    public function setFuelType($fuelType)
    {
        $this->fuelType = $fuelType;
    }

    /**
     * @return string
     */
    public function getMotTestCount()
    {
        return $this->motTestCount;
    }

    /**
     * @param string $motTestCount
     */
    public function setMotTestCount($motTestCount)
    {
        $this->motTestCount = $motTestCount;
        return $this;
    }

    public function hasMotTests()
    {
        return ($this->motTestCount == 0)? false : true;
    }

    /**
     * @return string
     */
    public function getLastMotTestDate()
    {
        return $this->lastMotTestDate;
    }

    /**
     * @param string $lastMotTestData
     */
    public function setLastMotTestDate($lastMotTestDate)
    {
        $this->lastMotTestDate = $lastMotTestDate;
        return $this;
    }

    public function getReadableLastMotTestDate()
    {
        if (!empty($this->lastMotTestDate)) {
            return DateTimeDisplayFormat::date(new \DateTime($this->getLastMotTestDate()));
        }

        return self::NOT_TESTED;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    public function addResult(VehicleSearchResult $vehicleSearchResult)
    {
        $this->results[] = $vehicleSearchResult;
        return $this;
    }

    /**
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @return int
     */
    public function getResultsCount()
    {
        return count($this->getResults());
    }

    /**
     * @return bool
     */
    public function isDvlaVehicle()
    {
        return $this->isDvlaVehicle;
    }

    /**
     * @param int $isDvlaVehicle
     */
    public function setIsDvlaVehicle($isDvlaVehicle)
    {
        $this->isDvlaVehicle = $isDvlaVehicle;
        return $this;
    }

    /**
     * @return string
     */
    public function getRetestEligibility()
    {
        return $this->retestEligibility;
    }

    /**
     * @param string $retestEligibility
     */
    public function setRetestEligibility($retestEligibility)
    {
        if (is_null($retestEligibility)) {
            $retestEligibility = false;
        }

        $this->retestEligibility = $retestEligibility;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return ($this->isDvlaVehicle()) ? VehicleSearchSource::DVLA : VehicleSearchSource::VTR;
    }

    /**
     * @return bool
     */
    public function isRetest()
    {
        return $this->retestEligibility;
    }

    /**
     * @return bool
     */
    public function isNormalTest()
    {
        return !$this->retestEligibility;
    }

    /**
     * @return bool
     */
    public function isIncognito()
    {
        return $this->isIncognito;
    }
}
