<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\UserService;

class QualificationDetailsController extends BaseTestSupportRestfulController
{
    public function __construct()
    {
    }

    public function create($data)
    {
        /** @var UserService $accountHelper */
        $accountHelper = $this->getServiceLocator()->get(UserService::class);

        return TestDataResponseHelper::jsonOk($accountHelper->addQualificationDetails($data));
    }
}
