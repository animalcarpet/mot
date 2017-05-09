<?php

namespace Vehicle\UpdateVehicleProperty\Action;

use Core\Action\ViewActionResult;
use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;

class AbstractUpdateVehicleAction implements AutoWireableInterface
{
    private $editStepAction;
    private $process;
    private $vehicleService;
    private $paramObfuscator;
    /** @var StartTestChangeService */
    private $startTestChangeService;

    protected $template = 'vehicle/update-vehicle-property/edit';

    /**
     * AbstractUpdateVehicleAction constructor.
     *
     * @param EditStepAction             $editStepAction
     * @param SingleStepProcessInterface $engineProcess
     * @param VehicleService             $vehicleService
     * @param ParamObfuscator            $paramObfuscator
     * @param StartTestChangeService     $startTestChangeService
     */
    public function __construct(
        EditStepAction $editStepAction,
        SingleStepProcessInterface $engineProcess,
        VehicleService $vehicleService,
        ParamObfuscator $paramObfuscator,
        StartTestChangeService $startTestChangeService
    ) {
        $this->editStepAction = $editStepAction;
        $this->process = $engineProcess;
        $this->vehicleService = $vehicleService;
        $this->paramObfuscator = $paramObfuscator;
        $this->startTestChangeService = $startTestChangeService;
    }

    public function execute($isPost, $obfuscatedVehicleId, $formData, $requestUrl)
    {
        $vehicleId = (int) $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId);
        $vehicle = $this->getVehicle($vehicleId);

        $result = $this->editStepAction->execute(
            $isPost,
            $this->process,
            new UpdateVehicleContext(
                $vehicle,
                $obfuscatedVehicleId,
                $requestUrl
            ),
            null,
            $formData
        );

        if ($result instanceof ViewActionResult) {
            $result->setTemplate($this->template);
        }

        return $result;
    }

    /**
     * @param $vehicleId
     *
     * @return \Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle|\Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle
     *
     * @throws \Exception
     */
    private function getVehicle($vehicleId)
    {
        try {
            if ($this->startTestChangeService->isDvlaVehicle()) {
                $vehicle = $this->vehicleService->getDvlaVehicleById($vehicleId);
            } else {
                $vehicle = $this->vehicleService->getDvsaVehicleById($vehicleId);
            }
        } catch (\Exception $exception) {
            throw new \Exception(
                'No vehicle with id '.$vehicleId.' found'
            );
        }

        return $vehicle;
    }
}
