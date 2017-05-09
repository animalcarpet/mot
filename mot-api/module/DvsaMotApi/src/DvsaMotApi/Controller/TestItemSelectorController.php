<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * Class TestItemSelectorController.
 */
class TestItemSelectorController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($id)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
        $testItemSelectorId = $this->params()->fromRoute('tisId', null);

        //  --  get mot test --
        /** @var MotTestDto $motTest */
        $motTest = $this->getMotTestService()->getMotTestData($motTestNumber);

        $vehicleClassCode = null;
        if ($motTest->getVehicle() !== null) {
            /** @var \DvsaCommon\Dto\Vehicle\VehicleDto $vehicle */
            $vehicle = $motTest->getVehicle();
            $vehicleClassCode = $vehicle->getClassCode();
        }

        //  --  get items   --
        $items = $this->getTestItemSelectorService()->getTestItemSelectorsData($testItemSelectorId, $vehicleClassCode);
        foreach ($items as $index => $item) {
            $items[$index] = array_merge($item, ['motTest' => $motTest]);
        }

        return ApiResponse::jsonOk($items);
    }

    /**
     * @return TestItemSelectorService
     */
    protected function getTestItemSelectorService()
    {
        return $this->getServiceLocator()->get('TestItemSelectorService');
    }

    /**
     * @return \DvsaMotApi\Service\MotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get('MotTestService');
    }
}
