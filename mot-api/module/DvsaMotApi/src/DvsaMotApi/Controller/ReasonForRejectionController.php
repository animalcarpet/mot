<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestItemSelectorService;
use Zend\View\Model\JsonModel;

/**
 * Class ReasonForRejectionController.
 */
class ReasonForRejectionController extends AbstractDvsaRestfulController
{
    private $testItemSelectorService;

    public function __construct(TestItemSelectorService $testItemSelectorService)
    {
        $this->testItemSelectorService = $testItemSelectorService;
    }

    public function getList()
    {
        $rfrs = $this->testItemSelectorService->getAllReasonsForRejection();
        $data = $this->testItemSelectorService->formatReasonsForRejectionForElasticSearch($rfrs);
        return ApiResponse::jsonOk($data);
    }
}
