<?php

namespace Vehicle\UpdateVehicleProperty\Context;

use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class UpdateVehicleContext implements FormContextInterface
{
    private $vehicle;

    /**
     * @var string
     */
    private $obfuscatedVehicleId;

    public function __construct( DvsaVehicle $vehicle, $obfuscatedVehicleId)
    {
        $this->vehicle = $vehicle;
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
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