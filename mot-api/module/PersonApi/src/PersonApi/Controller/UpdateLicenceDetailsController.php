<?php

namespace PersonApi\Controller;

use DvsaCommon\Enum\LicenceTypeCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\LicenceDetailsService;
use Exception;

class UpdateLicenceDetailsController extends AbstractDvsaRestfulController
{
    /**
     * @var LicenceDetailsService
     */
    private $licenceService;

    public function __construct(LicenceDetailsService $service)
    {
        $this->licenceService = $service;
    }

    /**
     * Create a driving licence for a user
     * $data should contain 'LicenceNumber' & 'LicenceRegion'.
     *
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $personId = $this->params()->fromRoute('id');

        $data['LicenceType'] = LicenceTypeCode::DRIVING_LICENCE;

        try {
            $this->licenceService->updateOrCreate((int) $personId, $data);
        } catch (Exception $e) {
            return ApiResponse::jsonError($e->getMessage());
        }

        return ApiResponse::jsonOk();
    }

    /**
     * Delete a user's driving licence.
     *
     * @param mixed $personId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($personId)
    {
        $this->licenceService->delete((int) $personId);

        return ApiResponse::jsonOk();
    }
}
