<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonService;

/**
 * Class PersonSiteCountController.
 */
class PersonSiteCountController extends AbstractDvsaRestfulController
{
    protected $personService;

    public function __construct(PersonService $service)
    {
        $this->personService = $service;
    }

    /**
     * @param mixed $personId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($personId)
    {
        $data = $this->personService->getPersonSiteCountAsTester($personId);

        return ApiResponse::jsonOk($data);
    }
}
