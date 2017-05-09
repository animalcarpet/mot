<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\CertificateChangeService;

/**
 * Class CertChangeDiffTesterReasonController.
 */
class CertChangeDiffTesterReasonController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        /* @var $service CertificateChangeService */
        $service = $this->getServiceLocator()->get('CertificateChangeService');
        $reasons = $service->getDifferentTesterReasonsAsArray();

        return ApiResponse::jsonOk($reasons);
    }
}
