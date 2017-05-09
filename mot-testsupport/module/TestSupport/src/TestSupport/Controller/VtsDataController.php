<?php

namespace TestSupport\Controller;

use Zend\View\Model\JsonModel;
use TestSupport\Service\VtsService;

/**
 * Creates VTSes (sites) for use by tests.
 *
 * Should not be deployed in production.
 */
class VtsDataController extends BaseTestSupportRestfulController
{
    /**
     * @param array $data
     *
     * @return JsonModel
     */
    public function create($data)
    {
        $vtsService = $this->getServiceLocator()->get(VtsService::class);

        return $vtsService->create($data);
    }
}
