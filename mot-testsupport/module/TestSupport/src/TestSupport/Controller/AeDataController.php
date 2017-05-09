<?php

namespace TestSupport\Controller;

use TestSupport\Service\AEService;

/**
 * Creates AEs (organisations) for use by tests.
 *
 * Should not be deployed in production.
 */
class AeDataController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $aeService = $this->getServiceLocator()->get(AEService::class);

        return $aeService->create($data);
    }
}
