<?php

namespace UserApi\SpecialNotice\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class SpecialNoticeBroadcastController.
 */
class SpecialNoticeBroadcastController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $specialNoticeService = $this->getSpecialNoticeService();
        $specialNoticeService->addNewSpecialNotices();

        return ApiResponse::jsonOk(['success' => true]);
    }

    /**
     * @return SpecialNoticeService
     */
    private function getSpecialNoticeService()
    {
        return $this->getServiceLocator()->get(SpecialNoticeService::class);
    }
}
