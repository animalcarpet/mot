<?php

namespace TestSupport\Controller;

use TestSupport\Service\PasswordResetService;

/**
 * Password reset controller
 * Should not be deployed in production.
 */
class PasswordResetDataController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $passwordResetService = $this->getServiceLocator()->get(PasswordResetService::class);

        return $passwordResetService->create($data);
    }
}
