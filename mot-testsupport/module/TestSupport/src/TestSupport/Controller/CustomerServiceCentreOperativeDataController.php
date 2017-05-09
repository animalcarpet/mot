<?php

namespace TestSupport\Controller;

use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;
use TestSupport\Service\CSCOService;

/**
 * Creates User account with CUSTOMER_SERVICE_CENTRE_OPERATIVE role for use by tests.
 */
class CustomerServiceCentreOperativeDataController extends BaseTestSupportRestfulController
{
    /**
     * @param null|array $data including
     *                         "diff" string to differentiate scheme management users
     *
     * @return JsonModel username of new tester
     */
    public function create($data)
    {
        $cscoService = $this->getServiceLocator()->get(CSCOService::class);
        $resultJson = $cscoService->create($data);

        return $resultJson;
    }
}
