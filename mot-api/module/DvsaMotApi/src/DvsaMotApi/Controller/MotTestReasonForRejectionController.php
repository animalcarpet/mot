<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Controller;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestReasonForRejectionController.
 */
class MotTestReasonForRejectionController extends AbstractDvsaRestfulController
{
    const RFR_ID = "motTestRfrId";

    /**
     * @var DefectSentenceCaseConverter
     */
    private $defectSentenceCaseConverter;

    /**
     * MotTestReasonForRejectionController constructor.
     *
     * @param DefectSentenceCaseConverter $defectSentenceCaseConverter
     */
    public function __construct(DefectSentenceCaseConverter $defectSentenceCaseConverter)
    {
        $this->setIdentifierName(self::RFR_ID);
        $this->defectSentenceCaseConverter = $defectSentenceCaseConverter;
    }

    /**
     * Get reason for rejection from the database, as a DefectDto, using its id.
     *
     * @param mixed $motTestRfrId
     *
     * @throws BadRequestException
     * @throws NotFoundException
     *
     * @return JsonModel
     */
    public function get($motTestRfrId)
    {
        $service = $this->getRfrService();

        try {
            $reasonForRejection = $service->getDefect($motTestRfrId);
            $result = DefectDto::fromEntity($reasonForRejection, $this->defectSentenceCaseConverter);
        } catch (BadRequestException $e) {
            throw new BadRequestException($e->getErrors(), BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        return ApiResponse::jsonOk($result);
    }

    /**
     * @param mixed $data
     *
     * @return JsonModel
     */
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

    /**
     * @param mixed $id
     *
     * @return JsonModel
     */
    public function delete($id)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);
        $motTestRfrId = $this->params()->fromRoute('motTestRfrId', null);

        $this->getRfrService()->deleteReasonForRejectionById($motTestNumber, $motTestRfrId);

        return ApiResponse::jsonOk("successfully deleted Reason for Rejection");
    }

    /**
     * @return JsonModel|array
     */
    public function markAsRepairedAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)) {
            return $this->notFoundAction();
        }

        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $motTestRfrId = (int) $this->params()->fromRoute('motTestRfrId');

        $this->getRfrService()->markReasonForRejectionAsRepaired($motTestNumber, $motTestRfrId);

        return ApiResponse::jsonOk();
    }

    /**
     * @return JsonModel|array
     */
    public function undoMarkAsRepairedAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)) {
            return $this->notFoundAction();
        }

        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $motTestRfrId = (int) $this->params()->fromRoute('motTestRfrId');

        $this->getRfrService()->undoMarkReasonForRejectionAsRepaired($motTestNumber, $motTestRfrId);

        return ApiResponse::jsonOk();
    }

    /**
     * @param mixed $data
     *
     * @return JsonModel
     */
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
