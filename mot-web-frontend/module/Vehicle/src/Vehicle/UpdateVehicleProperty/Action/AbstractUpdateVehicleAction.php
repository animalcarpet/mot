<?php

namespace Vehicle\UpdateVehicleProperty\Action;

use Core\Action\ViewActionResult;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;

class AbstractUpdateVehicleAction implements AutoWireableInterface
{
    private $editStepAction;
    private $process;
    private $vehicleService;
    private $paramObfuscator;

    protected $template = 'vehicle/update-vehicle-property/edit';

    public function __construct(
        EditStepAction $editStepAction,
        SingleStepProcessInterface $engineProcess,
        VehicleService $vehicleService,
        ParamObfuscator $paramObfuscator
    )
    {
        $this->editStepAction = $editStepAction;
        $this->process = $engineProcess;
        $this->vehicleService = $vehicleService;
        $this->paramObfuscator = $paramObfuscator;
    }

    public function execute($isPost, $obfuscatedVehicleId, $formData)
    {
        $vehicleId = (int) $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId);
        $vehicle = $this->vehicleService->getDvsaVehicleById($vehicleId);

        $result = $this->editStepAction->execute(
            $isPost,
            $this->process,
            new UpdateVehicleContext(
                $vehicle,
                $obfuscatedVehicleId
            ),
            null,
            $formData
        );

        if ($result instanceof ViewActionResult) {
            $result->setTemplate($this->template);
        }

        return $result;
    }
}