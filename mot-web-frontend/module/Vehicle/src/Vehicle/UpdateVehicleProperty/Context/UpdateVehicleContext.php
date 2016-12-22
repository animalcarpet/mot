<?php

namespace Vehicle\UpdateVehicleProperty\Context;

use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class UpdateVehicleContext implements FormContextInterface
{
    private $vehicle;

    /**
     * @var string
     */
    private $obfuscatedVehicleId;

    /** @var  string */
    private $requestUrl;

    /**
     * UpdateVehicleContext constructor.
     *
     * @param DvsaVehicle|DvlaVehicle $vehicle
     * @param             $obfuscatedVehicleId
     * @param             $requestUrl
     */
    public function __construct( $vehicle, $obfuscatedVehicleId, $requestUrl)
    {
        $this->vehicle = $vehicle;
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
        $this->requestUrl = $requestUrl;
    }

    /**
     * @return bool
     */
    public function isUpdateVehicleDuringTest()
    {
        if (strpos($this->requestUrl, 'change-under-test') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getVehicleId()
    {
        return $this->vehicle->getId();
    }

    public function getVehicle()
    {
        return $this->vehicle;
    }
    
    /**
     * @return string
     */
    public function getObfuscatedVehicleId()
    {
        return $this->obfuscatedVehicleId;
    }
}