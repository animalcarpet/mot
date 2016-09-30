<?php

namespace Vehicle\UpdateVehicleProperty\Action;

use Core\TwoStepForm\EditStepAction;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Vehicle\UpdateVehicleProperty\Process\UpdateClassProcess;

class UpdateClassAction extends AbstractUpdateVehicleAction implements AutoWireableInterface
{
    public function __construct(
        EditStepAction $editStepAction,
        UpdateClassProcess $classProcess,
        VehicleService $vehicleService,
        ParamObfuscator $paramObfuscator
    )
    {
        parent::__construct($editStepAction, $classProcess, $vehicleService, $paramObfuscator);
    }
}