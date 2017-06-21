<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * Class TestItemSelectorController.
 */
class TestItemSelectorController extends AbstractDvsaRestfulController
{
    private $testItemSelectorService;
    private $motTestService;

    public function __construct(TestItemSelectorService $testItemSelectorService, MotTestService $motTestService)
    {
        $this->setIdentifierName('motTestNumber');
        $this->testItemSelectorService = $testItemSelectorService;
        $this->motTestService = $motTestService;
    }

    public function get($id)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
        $testItemSelectorId = $this->params()->fromRoute('tisId', null);

        //  --  get mot test --
        /** @var MotTestDto $motTest */
        $motTest = $this->motTestService->getMotTestData($motTestNumber);

        $vehicleClassCode = null;
        if ($motTest->getVehicle() !== null) {
            /** @var \DvsaCommon\Dto\Vehicle\VehicleDto $vehicle */
            $vehicle = $motTest->getVehicle();
            $vehicleClassCode = $vehicle->getClassCode();
        }

        //  --  get items   --
        $items = $this->testItemSelectorService->getTestItemSelectorsData($testItemSelectorId, $vehicleClassCode);
        foreach ($items as $index => $item) {
            $items[$index] = array_merge($item, ['motTest' => $motTest]);
        }

        return ApiResponse::jsonOk($items);
    }
}
