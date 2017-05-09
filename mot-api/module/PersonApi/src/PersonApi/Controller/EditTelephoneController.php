<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\TelephoneService;

class EditTelephoneController extends AbstractDvsaRestfulController
{
    const PHONE_NUMBER_FIELD = 'personTelephone';

    /**
     * @var TelephoneService
     */
    private $phoneService;

    /**
     * EditTelephoneController constructor.
     *
     * @param TelephoneService $service
     */
    public function __construct(TelephoneService $service)
    {
        $this->phoneService = $service;
    }

    /**
     * Update a user's telephone number.
     *
     * @param int   $personId
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function update($personId, $data)
    {
        $newNumber = $data[self::PHONE_NUMBER_FIELD];

        $this->phoneService->updatePhoneNumber($personId, $newNumber);

        return ApiResponse::jsonOk();
    }
}
