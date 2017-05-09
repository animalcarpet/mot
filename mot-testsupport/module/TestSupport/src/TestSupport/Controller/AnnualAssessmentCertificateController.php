<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\UserService;

class AnnualAssessmentCertificateController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        /** @var UserService $accountHelper */
        $accountHelper = $this->getServiceLocator()->get(UserService::class);

        return TestDataResponseHelper::jsonOk($accountHelper->addAnnualAssessmentCertificate($data));
    }
}
