<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\CreateMotTestService;

/**
 * Class RetestController.
 */
class RetestController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $data[CreateMotTestService::FIELD_MOT_TEST_TYPE] = MotTestTypeCode::RE_TEST;

        /** @var MotTestService $motTestService */
        $motTestService = $this->getServiceLocator()->get('MotTestService');
        $motTest = $motTestService->createMotTest($data);

        return ApiResponse::jsonOk(['motTestNumber' => $motTest->getNumber()]);
    }
}
