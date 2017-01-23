<?php

namespace DvsaMotTest\ViewModel;

use Dvsa\Mot\ApiClient\Resource\Item\Colour;
use Dvsa\Mot\ApiClient\Resource\Item\FuelType;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;

/**
 * Class StartTestConfirmationViewModel
 *
 * @package DvsaMotTest\Model
 */
class StartTestConfirmationViewModel
{
    const STRING_TEST = 'test';
    const STRING_RETEST = 'retest';
    const START_TEST_CONFIRMATION_ACTION = '/start-test-confirmation';
    const START_TRAINING_CONFIRMATION_ACTION = '/start-training-test-confirmation';
    const START_NON_MOT_CONFIRMATION_ACTION = '/start-non-mot-test-confirmation';

    /** @var string */
    private $method;

    /** @var string */
    private $obfuscatedVehicleId;

    /** @var bool */
    private $noRegistration;

    /** @var string */
    private $vehicleSource;

    /** @var bool */
    private $inProgressTestExists;

    /** @var string */
    private $searchVrm;

    /** @var string */
    private $searchVin;

    /** @var string */
    private $vin;

    /** @var string */
    private $registration;

    /** @var array */
    private $eligibilityNotices;

    /** @var bool */
    private $canRefuseToTest;

    /** @var bool */
    private $eligibleForRetest;

    /** @var bool */
    private $motContingency;

    /** @var bool */
    private $isMysteryShopper;

    /** @var string */
    private $makeAndModel;

    /** @var string */
    private $engine;

    /** @var string */
    private $compoundedColour;

    /** @var string */
    private $firstUsedDate;

    /** @var string */
    private $brakeTestWeight;

    /** @var string */
    private $countryOfRegistration;

    /** @var string */
    private $motTestClass;

    /** @var string */
    private $motExpirationDate;

    /** @var bool */
    private $noTestClassSetOnSubmission;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return bool
     */
    private function checkMethod()
    {
        if (!isset($this->method)) {
            throw new \LogicException("Method should be set first");
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isRetest()
    {
        $this->checkMethod();
        return $this->method === MotTestTypeCode::RE_TEST;
    }

    /**
     * @return bool
     */
    public function isNormalTest()
    {
        $this->checkMethod();
        return $this->method === MotTestTypeCode::NORMAL_TEST;
    }

    /**
     * @return bool
     */
    public function isTrainingTest()
    {
        $this->checkMethod();
        return $this->method === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
    }

    /**
     * @return string
     */
    public function getTestTypeString()
    {
        return ($this->isRetest())? self::STRING_RETEST : self::STRING_TEST;
    }

    /**
     * @return string
     */
    public function getObfuscatedVehicleId()
    {
        return $this->obfuscatedVehicleId;
    }

    /**
     * @return string
     */
    public function getVehicleId()
    {
        return $this->obfuscatedVehicleId;
    }

    /**
     * @param string $obfuscatedVehicleId
     * @return $this
     */
    public function setObfuscatedVehicleId($obfuscatedVehicleId)
    {
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNoRegistration()
    {
        return $this->noRegistration;
    }

    /**
     * @param boolean $noRegistration
     * @return $this
     */
    public function setNoRegistration($noRegistration)
    {
        $this->noRegistration = $noRegistration;
        return $this;
    }

    /**
     * @return int
     */
    public function getNoRegistration()
    {
        return ($this->noRegistration)? 1 : 0;
    }

    /**
     * @return string
     */
    public function getVehicleSource()
    {
        return $this->vehicleSource;
    }

    /**
     * @param string $vehicleSource
     * @return $this
     */
    public function setVehicleSource($vehicleSource)
    {
        $this->vehicleSource = $vehicleSource;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInProgressTestExists()
    {
        return $this->inProgressTestExists;
    }

    /**
     * @return bool
     */
    public function shouldShowChangeLinks()
    {
        if (!$this->isInProgressTestExists() && !$this->isTrainingTest()) {
            return true;
        }

        return false;
    }

    /**
     * @param boolean $inProgressTestExists
     * @return $this
     */
    public function setInProgressTestExists($inProgressTestExists)
    {
        $this->inProgressTestExists = $inProgressTestExists;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchVrm()
    {
        return $this->searchVrm;
    }

    /**
     * @param string $searchVrm
     * @return $this
     */
    public function setSearchVrm($searchVrm)
    {
        $this->searchVrm = $searchVrm;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchVin()
    {
        return $this->searchVin;
    }

    /**
     * @param string $searchVin
     * @return $this
     */
    public function setSearchVin($searchVin)
    {
        $this->searchVin = $searchVin;
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
     * @return $this
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
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
     * @return $this
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
        return $this;
    }

    /**
     * @return array
     */
    public function getEligibilityNotices()
    {
        return $this->eligibilityNotices;
    }

    /**
     * @param array $eligibilityNotices
     * @return $this
     */
    public function setEligibilityNotices($eligibilityNotices)
    {
        $this->eligibilityNotices = $eligibilityNotices;
        return $this;
    }

    /**
     * @return boolean
     */
    public function canRefuseToTest()
    {
        return $this->canRefuseToTest;
    }

    /**
     * @param boolean $isEligibleForRetest
     * @param boolean $canRefuseToTestAssertion
     * @return $this
     */
    public function setCanRefuseToTest($isEligibleForRetest, $canRefuseToTestAssertion)
    {
        $canRefuseToTest = (
            ($this->isNormalTest() || ($this->isRetest() && $isEligibleForRetest))
            && !$this->isInProgressTestExists()
            && $canRefuseToTestAssertion
        );
        $this->canRefuseToTest = $canRefuseToTest;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEligibleForRetest()
    {
        return $this->eligibleForRetest;
    }

    /**
     * @param boolean $eligibleForRetest
     */
    public function setEligibleForRetest($eligibleForRetest)
    {
        $this->eligibleForRetest = $eligibleForRetest;
    }

    /**
     * @return boolean
     */
    public function isMotContingency()
    {
        return $this->motContingency;
    }

    /**
     * @param boolean $motContingency
     */
    public function setMotContingency($motContingency)
    {
        $this->motContingency = $motContingency;
    }

    /**
     * @return string
     */
    public function getConfirmActionUrl()
    {
        $actionConfirm = self::START_TEST_CONFIRMATION_ACTION;
        $safeSource = $this->getSafeSource();

        if ($this->getMethod() === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $actionConfirm = self::START_TRAINING_CONFIRMATION_ACTION;
        }

        if ($this->getMethod() === MotTestTypeCode::NON_MOT_TEST) {
            $actionConfirm = self::START_NON_MOT_CONFIRMATION_ACTION;
        }

        $actionConfirm.= '/'. $this->getObfuscatedVehicleId() . '/'
            . $this->getNoRegistration() .
            (!empty($safeSource) ? '/' . $safeSource : '');

        return $actionConfirm;
    }

    /**
     * @return string
     */
    public function getSafeSource()
    {
        $safeSource = filter_var($this->getVehicleSource(), FILTER_VALIDATE_INT);
        if ($this->isRetest()) {
            if (!empty($this->eligibilityNotices)) {
                $safeSource = '1';
            }
        }
        if (!$safeSource) {
            $safeSource = '0';
        }
        return $safeSource;
    }

    public function isNonMotTest()
    {
        $this->checkMethod();
        return $this->method === MotTestTypeCode::NON_MOT_TEST;
    }

    /**
     * @param bool $isMysteryShopper
     * @return $this
     */
    public function setIsMysteryShopper($isMysteryShopper)
    {
        $this->isMysteryShopper = $isMysteryShopper;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMysteryShopper()
    {
        return $this->isMysteryShopper;
    }

    /**
     * @return string
     */
    public function getMakeAndModel()
    {
        return $this->makeAndModel;
    }

    /**
     * @param String $make
     * @param String $model
     * @return $this
     */
    public function setMakeAndModel($make, $model)
    {
        $this->makeAndModel = $make . ', ' . $model;
        return $this;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param FuelType $fuelType
     * @param String $cylinderCapacity
     * @return $this
     */
    public function setEngine(FuelType $fuelType, $cylinderCapacity)
    {
        $fuelTypeName = $fuelType ? $fuelType->getName() : "";
        $this->engine = $fuelTypeName;
        if ($this->shouldDisplayCylinderCapacity($fuelType, $cylinderCapacity)) {
            $this->engine = $this->engine . ', ' . $cylinderCapacity;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCompoundedColour()
    {
        return $this->compoundedColour;
    }

    /**
     * @param Colour $colour
     * @param Colour|null $secondaryColour
     * @return $this
     */
    public function setCompoundedColour(Colour $colour, Colour $secondaryColour = null)
    {
        $colourName = $colour ? $colour->getName() : "";
        $secondaryColourName = $secondaryColour ? $secondaryColour->getName() : "";
        if ($secondaryColourName == 'Not Stated' || empty($secondaryColourName)) {
            $this->compoundedColour = $colourName;
            return $this;
        }
        $this->compoundedColour = $colourName . ', ' . $secondaryColourName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstUsedDate()
    {
        return $this->firstUsedDate;
    }


    /**
     * @param String $firstUsedDate
     * @return $this
     */
    public function setFirstUsedDate($firstUsedDate)
    {
        $this->firstUsedDate = DateTimeDisplayFormat::textDate($firstUsedDate);
        return $this;
    }

    /**
     * @return string
     */
    public function getBrakeTestWeight()
    {
        return $this->brakeTestWeight;
    }

    /**
     * @param string $brakeTestWeight
     */
    public function setBrakeTestWeight($brakeTestWeight)
    {
        $this->brakeTestWeight = $brakeTestWeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @param string $countryOfRegistration
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;
    }

    /**
     * @return string
     */
    public function getMotTestClass()
    {
        return $this->motTestClass;
    }

    /**
     * @param string $motTestClass
     */
    public function setMotTestClass($motTestClass)
    {
        $this->motTestClass = empty($motTestClass) ? 'Unknown' : $motTestClass;
    }

    /**
     * @return string
     */
    public function getMotExpirationDate()
    {
        return $this->motExpirationDate;
    }

    /**
     * @param string $motExpirationDate
     */
    public function setMotExpirationDate($motExpirationDate)
    {
        $this->motExpirationDate = DateTimeDisplayFormat::textDate($motExpirationDate);;
    }

    /**
     * @return boolean
     */
    public function isClassUnset()
    {
        if (strpos($this->motTestClass, 'Unknown') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isNoTestClassSetOnSubmission()
    {
        return $this->noTestClassSetOnSubmission;
    }

    /**
     * @param boolean $noTestClassSetOnSubmission
     */
    public function setNoTestClassSetOnSubmission($noTestClassSetOnSubmission)
    {
        $this->noTestClassSetOnSubmission = $noTestClassSetOnSubmission;
    }

    /**
     * @param FuelType $fuelType
     * @param String $cylinderCapacity
     * @return bool
     */
    private function shouldDisplayCylinderCapacity(FuelType $fuelType, $cylinderCapacity)
    {
        $fuelTypeCodesWithOptionalCylinderCapacity = new FuelTypeAndCylinderCapacity();
        return strlen($cylinderCapacity) > 0
        && !in_array($fuelType->getCode(), $fuelTypeCodesWithOptionalCylinderCapacity->getAllFuelTypeCodesWithOptionalCylinderCapacity());
    }
}
