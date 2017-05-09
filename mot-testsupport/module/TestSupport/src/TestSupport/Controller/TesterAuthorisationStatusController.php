<?php

namespace TestSupport\Controller;

use DvsaCommon\Utility\ArrayUtils;
use TestSupport\FieldValidation;
use TestSupport\Service\TesterAuthorisationStatusService;
use Zend\View\Model\JsonModel;

/**
 * Modify the given person's aAuthorisation for testing Status.
 *
 * Should not be deployed in production.
 */
class TesterAuthorisationStatusController extends BaseTestSupportRestfulController
{
    /**
     * @param array $data including following fields:
     *                    - Mandatory   'person_id'         int   tester's person id
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
        FieldValidation::checkForRequiredFieldsInData(['personId', 'qualifications'], $data);

        /** @var TesterAuthorisationStatusService $testerService */
        $testerService = $this->getServiceLocator()->get(TesterAuthorisationStatusService::class);

        return $testerService->setTesterQualificationStatus(
            ArrayUtils::get($data, 'personId'),
            ArrayUtils::get($data, 'qualifications')
        );
    }
}
