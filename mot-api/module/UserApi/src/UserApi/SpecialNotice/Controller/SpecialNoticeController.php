<?php

namespace UserApi\SpecialNotice\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;
use PersonApi\Service\PersonService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class SpecialNoticeController.
 */
class SpecialNoticeController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('snId');
    }

    public function getList()
    {
        $person = $this->getPersonService()->getPersonById(
            $this->params()->fromRoute('id', null)
        );
        $specialNoticeService = $this->getSpecialNoticeService();
        $specialNotices = $specialNoticeService->listCurrentSpecialNoticesForUser(
            $person->getUsername()
        );

        return ApiResponse::jsonOk($specialNotices);
    }

    public function create($data)
    {
        if ($data['isAcknowledged'] === true) {
            $id = $this->params()->fromRoute('snId', null);

            $testerService = $this->getServiceLocator()->get('TesterService');

            $specialNoticeService = $this->getServiceLocator()->get(SpecialNoticeService::class);
            $specialNoticeService->markAcknowledged($id);
        } else {
            throw new BadRequestException('Special notice cannot be unacknowledged', 400);
        }

        return ApiResponse::jsonOk(['success' => true]);
    }

    /**
     * @return SpecialNoticeService
     */
    private function getSpecialNoticeService()
    {
        return $this->getServiceLocator()->get(SpecialNoticeService::class);
    }

    /**
     * @return PersonService
     */
    private function getPersonService()
    {
        return $this->getServiceLocator()->get(PersonService::class);
    }
}
