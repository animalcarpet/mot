<?php

namespace DvsaMotTest\Dto;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;

class MotPrintModelDto
{
    /** @var MotTest $motDetails */
    private $motDetails;

    /** @var int $motTestNumber */
    private $motTestNumber;

    /** @var bool $isDuplicate */
    private $isDuplicate;

    /** @var string $vehicleRegistration */
    private $vehicleRegistration;

    /**
     * MotPrintModelDto constructor.
     *
     * @param MotTest $motDetails
     * @param int $motTestNumber
     * @param string $vehicleRegistration
     * @param bool $isDuplicate
     */
    public function __construct($motDetails, $motTestNumber, $vehicleRegistration, $isDuplicate = false)
    {
        $this->motDetails = $motDetails;
        $this->motTestNumber = $motTestNumber;
        $this->vehicleRegistration = $vehicleRegistration;
        $this->isDuplicate = $isDuplicate;
    }

    /**
     * @return MotTest
     */
    public function getMotDetails()
    {
        return $this->motDetails;
    }

    /**
     * @param MotTest $motDetails
     */
    public function setMotDetails($motDetails)
    {
        $this->motDetails = $motDetails;
    }

    /**
     * @return int
     */
    public function getMotTestNumber()
    {
        return $this->motTestNumber;
    }

    /**
     * @param int $motTestNumber
     */
    public function setMotTestNumber($motTestNumber)
    {
        $this->motTestNumber = $motTestNumber;
    }

    /**
     * @return string
     */
    public function getVehicleRegistration()
    {
        return $this->vehicleRegistration;
    }

    /**
     * @param string $vehicleRegistration
     */
    public function setVehicleRegistration($vehicleRegistration)
    {
        $this->vehicleRegistration = $vehicleRegistration;
    }

    /**
     * @return bool
     */
    public function isDuplicate()
    {
        return $this->isDuplicate;
    }

    /**
     * @param bool $isDuplicate
     */
    public function setIsDuplicate($isDuplicate)
    {
        $this->isDuplicate = $isDuplicate;
    }
}