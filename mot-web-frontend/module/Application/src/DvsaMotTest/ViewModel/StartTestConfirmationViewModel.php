<?php

namespace DvsaMotTest\ViewModel;

use DvsaCommon\Enum\MotTestTypeCode;

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
     * @param boolean $inProgressTestExists
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
}
