<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use DvsaMotApi\Service\MotTestStatusChangeService;

/**
 * Mot Test Status Controller.
 */
class MotTestStatusController extends AbstractDvsaRestfulController
{
    /** @var MotTestStatusChangeService */
    private $statusChangeService;
    /** @var CertificateCreationService */
    private $certificateCreationService;
    /** @var MotTestStatusChangeNotificationService */
    private $statusChangeNotificationService;

    /**
     * @param MotTestStatusChangeService             $statusChangeService
     * @param CertificateCreationService             $certificateCreationService
     * @param MotTestStatusChangeNotificationService $statusChangeNotificationService
     */
    public function __construct(
        MotTestStatusChangeService $statusChangeService,
        CertificateCreationService $certificateCreationService,
        MotTestStatusChangeNotificationService $statusChangeNotificationService
    ) {
        $this->statusChangeService = $statusChangeService;
        $this->certificateCreationService = $certificateCreationService;
        $this->statusChangeNotificationService = $statusChangeNotificationService;
    }

    public function create($data)
    {
        /*
         * Please do not wrap this code in a transaction. The MOT status
         * update call needs to update the slot count in a separate transaction and
         * we don't have nested transactions.
         */
        $motTestNumber = $this->params('motTestNumber', null);

        $this->statusChangeNotificationService->captureMotTestBeforeUpdateStateById($motTestNumber);

        $motTestData = $this->statusChangeService->updateStatus($motTestNumber, $data);

        $this->statusChangeNotificationService->captureStateByIdAndSendNotificationIfApplicable($motTestNumber);

        try {
            $motTestData = $this->certificateCreationService->create(
                $motTestNumber,
                $motTestData,
                $this->getUserId()
            );
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return ApiResponse::jsonOk($motTestData);
    }
}
