<?php

namespace TestSupport\Controller;

use TestSupport\Service\CSMService;
use Zend\View\Model\JsonModel;

/**
 * Creates User account with CUSTOMER_SERVICE_MANAGER role for use by tests.
 */
class CustomerServiceManagerDataController extends BaseTestSupportRestfulController
{
    /**
     * @param null|array $data including
     *                         "diff" string to differentiate scheme management users
     *
     * @return JsonModel username of new tester
     */
    public function create($data)
    {
        $csmService = $this->getServiceLocator()->get(CSMService::class);
        $resultJson = $csmService->create($data);

        return $resultJson;
    }
}
