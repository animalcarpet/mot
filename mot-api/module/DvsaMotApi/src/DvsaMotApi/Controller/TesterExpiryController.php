<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\TesterExpiryService;

/**
 * Class TesterExpiryController.
 */
class TesterExpiryController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        /** @var TesterExpiryService $testerExpiryService */
        $testerExpiryService = $this->getServiceLocator()->get('TesterExpiryService');

        $testerExpiryService->changeStatusOfInactiveTesters();

        return ApiResponse::jsonOk(
            ['success' => true]
        );
    }
}
