<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;

/**
 * Personal authorisation for testing mot.
 */
class MotTestingAuthorisationController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonalAuthorisationForMotTestingService
     */
    protected $personalAuthorisationForMotTestingService;

    public function __construct(PersonalAuthorisationForMotTestingService $service)
    {
        $this->personalAuthorisationForMotTestingService = $service;
    }

    public function get($id)
    {
        return ApiResponse::jsonOk(
            $this->personalAuthorisationForMotTestingService->getPersonalTestingAuthorisation($id)->toArray()
        );
    }

    public function update($id, $data)
    {
        return ApiResponse::jsonOk(
            $this->personalAuthorisationForMotTestingService->updatePersonalTestingAuthorisationGroup($id, $data)
                ->toArray()
        );
    }
}
