<?php

namespace TestSupport\Controller;

use TestSupport\Service\VM10519UserService;

/**
 * Creates User account with VM-10519-USER role for use by tests.
 *
 * Should not be deployed in production.
 */
class VM10519UserDataController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        /** @var $service VM10519UserService */
        $service = $this->getServiceLocator()->get(VM10519UserService::class);
        $resultJson = $service->create($data);

        return $resultJson;
    }
}
