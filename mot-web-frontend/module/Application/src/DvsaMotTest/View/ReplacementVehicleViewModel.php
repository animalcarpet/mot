<?php

namespace DvsaMotTest\View;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Date\DateTimeDisplayFormat;

class ReplacementVehicleViewModel
{
    const DEFAULT_MODEL_NAME = 'Unknown';
    const MAKE_MODEL_OTHER = 'other';

    private $vin;
    private $vrm;
    private $primaryColourId;
    private $secondaryColourId;
    private $countryOfRegistration;
    private $displayModelBody;
    private $expiryDate;

    /** @var bool */
    private $isLatestPassedMotTest;

    /** @var ReplacementMakeViewModel $make */
    private $make;

    /** @var ReplacementModelViewModel $model */
    private $model;

    public function __construct($data)
    {
        $primaryColour = ArrayUtils::tryGet($data, 'primaryColour', []);
        $secondaryColour = ArrayUtils::tryGet($data, 'secondaryColour', []);

        $this->primaryColourId = ArrayUtils::tryGet($primaryColour, 'code');
        $this->secondaryColourId = ArrayUtils::tryGet($secondaryColour, 'code');

        $make = ArrayUtils::tryGet($data, 'make', []);
        $model = ArrayUtils::tryGet($data, 'model', []);

        $this->vin = ArrayUtils::tryGet($data, 'vin');
        $this->vrm = ArrayUtils::tryGet($data, 'vrm');

        $this->make = new ReplacementMakeViewModel($make);
        $this->model = new ReplacementModelViewModel($model);
        $this->countryOfRegistration = ArrayUtils::tryGet($data, 'countryOfRegistration');
        $this->expiryDate = ArrayUtils::tryGet($data, 'expiryDate');

        $this->isLatestPassedMotTest = ArrayUtils::tryGet($data, 'isLatestPassedMotTest');
    }

    public function isLatestPassedMotTest()
    {
        return $this->isLatestPassedMotTest;
    }

    /**
     * @param ReplacementMakeViewModel $make
     *
     * @return $this
     */
    public function setMake(ReplacementMakeViewModel $make)
    {
        $this->make = $make;

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
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * @return ReplacementModelViewModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return ReplacementMakeViewModel
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @return int
     */
    public function getPrimaryColourId()
    {
        return $this->primaryColourId;
    }

    /**
     * @return int
     */
    public function getSecondaryColourId()
    {
        return $this->secondaryColourId;
    }

    /**
     * @return string
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return string
     */
    public function getExpiryDisplayDate()
    {
        if (!empty($this->getExpiryDate())) {
            return DateTimeDisplayFormat::date(new \DateTime($this->expiryDate));
        }

        return null;
    }

    public function getExpiryDateDay()
    {
        if (!empty($this->getExpiryDate())) {
            return (new \DateTime($this->expiryDate))->format('d');
        }

        return null;
    }

    public function getExpiryDateMonth()
    {
        if (!empty($this->getExpiryDate())) {
            return (new \DateTime($this->expiryDate))->format('m');
        }

        return null;
    }

    public function getExpiryDateYear()
    {
        if (!empty($this->getExpiryDate())) {
            return (new \DateTime($this->expiryDate))->format('Y');
        }

        return null;
    }

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setDisplayModelBody($bool = false)
    {
        $this->displayModelBody = $bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function displayModelBody()
    {
        if ($this->getModel()->getName() == self::DEFAULT_MODEL_NAME) {
            return true;
        }

        if (!empty($this->getMake()->getId()) && empty($this->getModel()->getName())) {
            return true;
        }

        if ($this->displayModelBody) {
            return true;
        }

        return false;
    }

    public function displayMakeInputBox()
    {
        return !$this->displayModelBody;
    }

    public function isOtherMake()
    {
        return $this->getMake()->getCode() == self::MAKE_MODEL_OTHER;
    }
}
