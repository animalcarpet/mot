<?php

namespace TestSupport\Controller;

use Zend\View\Model\JsonModel;
use TestSupport\Service\TesterService;

/**
 * Creates testers for use by tests.
 *
 * Should not be deployed in production.
 */
class TesterDataController extends BaseTestSupportRestfulController
{
    /**
     * @param array $data including following fields:
     *                    - Mandatory   'siteIds'           array   List of VTSs to be associated with the tester
     *                    - Optional    'diff'              string  A custom value to be used as the username instead of randomly
     *                    generated one
     *                    - Optional    'qualifications'    array   List of testing groups and tester's qualification for each
     *                    as its key,value pairs
     *                    e.g. ['A'=> 'QLFD' , 'B' => 'DMTN']
     *
     * @see DvsaCommon\Enum\VehicleClassGroupCode
     * @see DvsaCommon\Enum\AuthorisationForTestingMotStatusCode
     *
     * @return JsonModel
     */
    public function create($data)
    {
        /** @var TesterService $testerService */
        $testerService = $this->getServiceLocator()->get(TesterService::class);
        $resultJson = $testerService->create($data);

        return $resultJson;
    }
}
