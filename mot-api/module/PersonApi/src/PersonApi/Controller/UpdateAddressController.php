<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonAddressService;

class UpdateAddressController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonAddressService
     */
    protected $personAddressService;

    public function __construct(PersonAddressService $personAddressService)
    {
        $this->personAddressService = $personAddressService;
    }

    public function create($data)
    {
        $personId = $this->params()->fromRoute('id');

        $addressResponse = $this->personAddressService->update($personId, $data);

        return ApiResponse::jsonOk($addressResponse);
    }
}
