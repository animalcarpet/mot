<?php

namespace Vehicle\UpdateVehicleProperty\Context;

use Core\FormWizard\WizardContextInterface;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Resource\Item\AbstractVehicle;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;

class UpdateVehicleContext extends Context implements FormContextInterface, WizardContextInterface
{
    private $vehicle;

    /**
     * @var string
     */
    private $obfuscatedVehicleId;

    /** @var string */
    private $requestUrl;

    /**
     * UpdateVehicleContext constructor.
     *
     * @param AbstractVehicle $vehicle
     * @param                 $obfuscatedVehicleId
     * @param                 $requestUrl
     */
    public function __construct(AbstractVehicle $vehicle, $obfuscatedVehicleId, $requestUrl)
    {
        parent::__construct($vehicle, $obfuscatedVehicleId);
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
