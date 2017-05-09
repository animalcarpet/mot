<?php

namespace TestSupport\Controller;

use TestSupport\Service\TwoFactorAuthCardService;

class TwoFactorAuthCardController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        /** @var TwoFactorAuthCardService $service */
        $service = $this->getServiceLocator()->get(TwoFactorAuthCardService::class);

        return $service->create();
    }
}
