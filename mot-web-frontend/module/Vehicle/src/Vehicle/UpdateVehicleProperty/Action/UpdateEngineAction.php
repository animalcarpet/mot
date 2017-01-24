<?php

namespace Vehicle\UpdateVehicleProperty\Action;

use Core\TwoStepForm\EditStepAction;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Process\UpdateEngineProcess;

class UpdateEngineAction extends AbstractUpdateVehicleAction implements AutoWireableInterface
{
    public function __construct(
        EditStepAction $editStepAction,
        UpdateEngineProcess $engineProcess,
        VehicleService $vehicleService,
        ParamObfuscator $paramObfuscator,
        StartTestChangeService $startTestChangeService
    )
    {
        parent::__construct($editStepAction, $engineProcess, $vehicleService, $paramObfuscator, $startTestChangeService);
    }
}