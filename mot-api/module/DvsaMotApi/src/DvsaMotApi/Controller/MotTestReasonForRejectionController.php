<?php
namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestReasonForRejectionController
 */
class MotTestReasonForRejectionController extends AbstractDvsaRestfulController
{
    const RFR_ID = "motTestRfrId";

    public function __construct()
    {
        $this->setIdentifierName(self::RFR_ID);
    }

    /**
     * Get reason for rejection from the database, as a DefectDto, using its id.
     *
     * @param mixed $motTestRfrId
     *
     * @return JsonModel
     *
     * @throws BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($motTestRfrId)
    {
        $service = $this->getRfrService();

        try {
            $reasonForRejection = $service->getDefect($motTestRfrId);
            $result = DefectDto::fromEntity($reasonForRejection);
        } catch (BadRequestException $e) {
            throw new BadRequestException($e->getErrors(), BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        return ApiResponse::jsonOk($result);
    }

    public function create($data)
    {
        $service = $this->getRfrService();

        if (isset($data['id'])) {
            $service->editReasonForRejection($data['id'], $data);

            return ApiResponse::jsonOk("successfully updated Reason for Rejection");
        } else {
            $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
            $motTest = $this->getMotTestService()->getMotTest($motTestNumber);

            $result = $service->addReasonForRejection($motTest, $data);
            
            return ApiResponse::jsonOk($result);
        }
    }

    public function delete($id)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
        $motTestRfrId = $this->params()->fromRoute('motTestRfrId', null);

        $this->getRfrService()->deleteReasonForRejectionById($motTestNumber, $motTestRfrId);

        return ApiResponse::jsonOk("successfully deleted Reason for Rejection");
    }

    public function deleteList($data)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
        $motTestRfrId = $this->params()->fromRoute('motTestRfrId', null);

        $this->getRfrService()->deleteReasonForRejectionById($motTestNumber, $motTestRfrId);

        return ApiResponse::jsonOk("successfully deleted Reason for Rejection");
    }

    /**
     * @return MotTestReasonForRejectionService
     */
    private function getRfrService()
    {
        return $this->getServiceLocator()->get(MotTestReasonForRejectionService::class);
    }

    /**
     * @return \DvsaMotApi\Service\MotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get('MotTestService');
    }
}
