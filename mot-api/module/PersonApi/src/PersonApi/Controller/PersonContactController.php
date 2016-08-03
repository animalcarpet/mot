<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonContactService;
use Zend\View\Model\JsonModel;

/**
 * Class PersonContactController
 *
 * @package PersonApi\Controller
 */
class PersonContactController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonContactService
     */
    protected $personContactService;

    public function __construct(PersonContactService $service)
    {
        $this->personContactService = $service;
    }

    public function patch($id, $data)
    {
        $details = $this->personContactService->updateEmailForPerson($id, $data);
        return ApiResponse::jsonOk($details);
    }
}
