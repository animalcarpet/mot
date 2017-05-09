<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\HelpDesk\Service\HelpDeskPersonService;

/**
 * Class PersonProfileRestrictedController.
 */
class PersonProfileRestrictedController extends AbstractDvsaRestfulController
{
    /**
     * @var HelpDeskPersonService
     */
    protected $helpDeskPersonService;

    public function __construct(HelpDeskPersonService $service)
    {
        $this->helpDeskPersonService = $service;
    }

    /**
     * @param int $personId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($personId)
    {
        $person = $this->helpDeskPersonService->getPersonProfile($personId, true);

        return ApiResponse::jsonOk($person->toArray());
    }
}
