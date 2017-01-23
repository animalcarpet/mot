<?php
namespace Vehicle\UpdateVehicleProperty\Form\Wizard;

use Core\FormWizard\WizardContextInterface;
use Dvsa\Mot\ApiClient\Resource\Item\AbstractVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class Context implements WizardContextInterface
{
    private $obfuscatedVehicleId;
    private $vehicle;
    private $makeId;

    public function __construct(AbstractVehicle $vehicle, $obfuscatedVehicleId)
    {
        $this->vehicle = $vehicle;
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
    }

    /**
     * @return string
     */
    public function getObfuscatedVehicleId()
    {
        return $this->obfuscatedVehicleId;
    }

    /**
     * @param string $obfuscatedVehicleId
     * @return Context
     */
    public function setObfuscatedVehicleId($obfuscatedVehicleId)
    {
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
        return $this;
    }

    /**
     * @return AbstractVehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * @param AbstractVehicle $vehicle
     * @return Context
     */
    public function setVehicle(AbstractVehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        return $this;
    }
}