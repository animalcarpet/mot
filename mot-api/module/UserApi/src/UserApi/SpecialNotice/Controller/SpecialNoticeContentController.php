<?php

namespace UserApi\SpecialNotice\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class SpecialNoticeContentController.
 */
class SpecialNoticeContentController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $listRemoved = $this->params()->fromQuery('removed', false);
        $listAll = $this->params()->fromQuery('listAll', false);
        if ($listRemoved) {
            return ApiResponse::jsonOk($this->getSpecialNoticeService()->getRemovedSpecialNotices());
        } elseif ($listAll) {
            return ApiResponse::jsonOk($this->getSpecialNoticeService()->getAllSpecialNotices());
        } else {
            return ApiResponse::jsonOk($this->getSpecialNoticeService()->getAllCurrentSpecialNotices());
        }
    }

    public function get($id)
    {
        return ApiResponse::jsonOk($this->getSpecialNoticeService()->getSpecialNoticeContentForUser($id));
    }

    public function delete($id)
    {
        $this->getSpecialNoticeService()->removeSpecialNoticeContent($id);

        return ApiResponse::jsonOk(['success' => true]);
    }

    public function create($data)
    {
        $specialNoticeContent = $this->getSpecialNoticeService()->createSpecialNotice($data);

        return ApiResponse::jsonOk($specialNoticeContent);
    }

    public function update($id, $data)
    {
        $content = $this->getSpecialNoticeService()->update($id, $data);
        $json = $this->getSpecialNoticeService()->extractContent($content);

        return ApiResponse::jsonOk($json);
    }

    public function publishAction()
    {
        $id = $this->params()->fromRoute('id');
        $result = $this->getSpecialNoticeService()->publish($id);
        $json = $this->getSpecialNoticeService()->extractContent($result);

        return ApiResponse::jsonOk($json);
    }

    /**
     * @return SpecialNoticeService
     */
    private function getSpecialNoticeService()
    {
        return $this->getServiceLocator()->get(SpecialNoticeService::class);
    }
}
