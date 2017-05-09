<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonService;

/**
 * Class PersonController.
 */
class PersonController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonService
     */
    protected $personService;

    public function __construct(PersonService $personService)
    {
        $this->personService = $personService;
    }

    /**
     * @param mixed $personId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($personId)
    {
        $data = $this->personService->getPerson($personId);

        return ApiResponse::jsonOk($data);
    }
}
