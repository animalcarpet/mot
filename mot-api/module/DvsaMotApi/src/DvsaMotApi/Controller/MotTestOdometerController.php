<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Controller;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\OdometerReadingQueryService;
use DvsaMotApi\Service\OdometerReadingUpdatingService;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestOdometerController.
 */
class MotTestOdometerController extends AbstractDvsaRestfulController implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    /**
     * @param string $motTestNumber
     * @param array  $data
     *
     * @return JsonModel
     */
    public function update($motTestNumber, $data)
    {
        $value = ArrayUtils::tryGet($data, 'value');
        $unit = ArrayUtils::tryGet($data, 'unit');
        $resultType = ArrayUtils::tryGet($data, 'resultType');
        $odometerReading = OdometerReadingDto::create()
            ->setValue($value)->setUnit($unit)->setResultType($resultType);

        $motTest = $this->getMotTest($motTestNumber);

        // POC, will be refactored into final transaction handling
        $this->inTransaction(
            function () use (&$motTest, &$odometerReading) {

                /* @var OdometerReadingUpdatingService $updatingService */
                $updatingService = $this->getServiceLocator()->get('OdometerReadingUpdatingService');

                $updatingService->updateForMotTest($odometerReading, $motTest);
            }
        );

        return ApiResponse::jsonOk();
    }

    public function canModifyOdometerAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        /**
         * @var MotTestSecurityService
         */
        $motTestSecurityService = $this->getServiceLocator()->get('MotTestSecurityService');
        $canModify = $motTestSecurityService->canModifyOdometerForTest($motTestNumber);

        return ApiResponse::jsonOk(['modifiable' => $canModify]);
    }

    /**
     * @return JsonModel
     */
    public function getNoticesAction()
    {
        /**
         * @var OdometerReadingQueryService
         */
        $queryService = $this->getServiceLocator()->get('OdometerReadingQueryService');
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $notices = $queryService->getNotices($motTestNumber);

        return ApiResponse::jsonOk($notices);
    }

    /**
     * @param $motTestNumber
     *
     * @return \DvsaEntities\Entity\MotTest
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function getMotTest($motTestNumber)
    {
        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->getServiceLocator()->get(MotTestRepository::class);

        return $motTestRepository->getMotTestByNumber($motTestNumber);
    }
}
