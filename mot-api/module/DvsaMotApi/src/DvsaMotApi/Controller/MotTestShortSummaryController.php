<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\MotTestShortSummaryService;

class MotTestShortSummaryController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($motTestNumber)
    {
        $motTestData = $this->getMotTestService()->getMotTestData($motTestNumber);

        return ApiResponse::jsonOk($motTestData);
    }

    /**
     * @return MotTestShortSummaryService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get('MotTestShortSummaryService');
    }
}
