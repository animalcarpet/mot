<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonalDetailsService;

/**
 * Class PersonalDetailsController.
 */
class PersonalDetailsController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonalDetailsService
     */
    protected $personalDetailsService;

    public function __construct(PersonalDetailsService $service)
    {
        $this->personalDetailsService = $service;
    }

    public function get($personId)
    {
        $personalDetailsObj = $this->personalDetailsService->get($personId);

        return ApiResponse::jsonOk($personalDetailsObj->toArray());
    }

    public function update($personId, $data)
    {
        $personalDetailsObj = $this->personalDetailsService->update($personId, $data);

        return ApiResponse::jsonOk($personalDetailsObj->toArray());
    }
}
