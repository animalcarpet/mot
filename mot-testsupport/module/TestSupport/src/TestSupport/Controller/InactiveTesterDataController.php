<?php

namespace TestSupport\Controller;

use TestSupport\Service\InactiveTesterService;
use Zend\View\Model\JsonModel;

/**
 * Creates inactive testers for use by tests.
 *
 * Should not be deployed in production.
 */
class InactiveTesterDataController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data
     *
     * @return JsonModel
     */
    public function create($data)
    {
        /** @var InactiveTesterService $inactiveTesterService */
        $inactiveTesterService = $this->getServiceLocator()->get(InactiveTesterService::class);
        $resultJson = $inactiveTesterService->create($data);

        return $resultJson;
    }
}
