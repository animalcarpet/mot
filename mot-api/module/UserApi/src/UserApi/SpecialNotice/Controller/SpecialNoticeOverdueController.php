<?php

namespace UserApi\SpecialNotice\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

class SpecialNoticeOverdueController extends AbstractDvsaRestfulController
{
    /**
     * @var SpecialNoticeService
     */
    private $specialNoticeService;

    public function __construct(SpecialNoticeService $specialNoticeService)
    {
        $this->specialNoticeService = $specialNoticeService;
    }

    public function getList()
    {
        $result = $this->specialNoticeService->getAmountOfOverdueSpecialNoticesForClasses();

        return ApiResponse::jsonOk($result);
    }
}
