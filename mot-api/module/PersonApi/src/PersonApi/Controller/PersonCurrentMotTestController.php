<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonService;

/**
 * Class PersonCurrentMotTestController.
 */
class PersonCurrentMotTestController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonService
     */
    protected $personService;

    public function __construct(PersonService $service)
    {
        $this->personService = $service;
    }

    public function get($personId)
    {
        $data = $this->personService->getCurrentMotTestIdByPersonId($personId);

        return ApiResponse::jsonOk($data);
    }
}
