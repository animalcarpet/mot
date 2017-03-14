<?php
namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\VehicleTestingAdviceService;

class VehicleTestingAdviceController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        /** @var VehicleTestingAdviceService $vehicleExaminerService */
        $vehicleExaminerService = $this->getServiceLocator()->get(VehicleTestingAdviceService::class);
        $vehicleExaminerService->createDefault($data["vehicle_id"], $data["model_id"]);

        return TestDataResponseHelper::jsonOk($data);
    }
}
