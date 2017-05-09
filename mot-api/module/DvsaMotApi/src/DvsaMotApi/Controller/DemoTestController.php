<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\CreateMotTestService;

/**
 * Api controller for creating a new DEMO Test.
 */
class DemoTestController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $data[CreateMotTestService::FIELD_MOT_TEST_TYPE] = MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
        $motTest = $this->getService()->createMotTest($data);

        return ApiResponse::jsonOk(['motTestNumber' => $motTest->getNumber()]);
    }

    /**
     * @return MotTestService
     */
    private function getService()
    {
        return $this->getServiceLocator()->get('MotTestService');
    }
}
